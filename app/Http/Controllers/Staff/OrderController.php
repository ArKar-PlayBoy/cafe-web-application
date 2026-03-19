<?php

namespace App\Http\Controllers\Staff;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use App\Models\OrderRejection;
use App\Services\StockService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        // Show orders that either:
        // 1. Are COD (payment collected on delivery)
        // 2. Have verified/paid payment
        // 3. Have a payment screenshot uploaded (awaiting verification)
        // 4. Are KBZ Pay orders waiting for payment (payment_status = awaiting_verification)
        // 5. Are Stripe orders (auto-verified payment)
        $orders = Order::with('user', 'items.menuItem', 'rejection')
            ->where(function ($query) {
                $query->where('payment_method', 'cod')
                    ->orWhere('payment_method', 'stripe')
                    ->orWhereIn('payment_status', ['verified', 'paid'])
                    ->orWhereNotNull('payment_screenshot')
                    ->orWhere(function ($q) {
                        $q->where('payment_method', 'kbz_pay')
                            ->where('payment_status', 'awaiting_verification');
                    });
            })
            ->latest()
            ->paginate(15);

        return view('staff.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $this->authorize('orders.manage');
        $request->validate([
            'status' => 'required|in:pending,preparing,ready,completed,cancelled,confirmed',
        ]);

        $previousStatus = $order->status;

        $order->update(['status' => $request->status]);

        // For COD orders, send confirmation email when status changes to 'preparing'
        // This is when the order is officially confirmed and kitchen starts preparing
        if ($order->payment_method === 'cod' && $request->status === 'preparing') {
            event(new OrderCreated($order));
        } else {
            // Dispatch status change event for other status updates
            event(new OrderStatusChanged($order, $previousStatus, $request->status));
        }

        if ($request->status === 'completed' && $previousStatus !== 'completed') {
            StockService::deductStock($order);

            if ($order->user && in_array($order->payment_status, ['verified', 'paid'])) {
                $order->user->updateOrderStats($order->total);
            }
        }

        return back()->with('success', 'Order status updated successfully.');
    }

    public function reject(Request $request, Order $order)
    {
        $this->authorize('orders.cancel');

        $request->validate([
            'reason' => 'required|string|max:255',
            'note' => 'nullable|string|max:500',
        ]);

        OrderRejection::create([
            'order_id' => $order->id,
            'user_id' => auth('staff')->id(),
            'reason' => $request->reason,
            'note' => $request->note,
        ]);

        $previousStatus = $order->status;
        $order->update(['status' => 'cancelled']);

        // Dispatch status change event for email notification
        event(new OrderStatusChanged($order, $previousStatus, 'cancelled'));

        return back()->with('success', 'Order rejected successfully.');
    }

    public function verifyPayment(Order $order)
    {
        $this->authorize('orders.verify_payment');

        // Fix: Check for 'pending' or 'awaiting_verification' status
        if (! $order->payment_screenshot || in_array($order->payment_status, ['verified', 'paid'])) {
            return back()->with('error', 'No payment screenshot to verify or already verified.');
        }

        $previousStatus = $order->payment_status;

        $order->update([
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'payment_verified_by' => auth('staff')->id(),
            'status' => 'preparing',
        ]);

        \Illuminate\Support\Facades\Log::info('Payment verified by staff', [
            'order_id' => $order->id,
            'previous_status' => $previousStatus,
            'new_status' => 'verified',
            'staff_id' => auth('staff')->id(),
        ]);

        // Create kitchen ticket after payment verified
        KitchenTicket::create([
            'order_id' => $order->id,
            'status' => 'new',
        ]);

        // Dispatch order confirmation email - KBZ Pay payment now verified
        event(new OrderCreated($order));

        return redirect()->route('staff.orders.view-screenshot', $order->id)
            ->with('success', 'Payment verified successfully.');
    }

    public function rejectPayment(Request $request, Order $order)
    {
        $this->authorize('orders.verify_payment');
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        // Fix: Check for statuses that can be rejected
        if (! $order->payment_screenshot || in_array($order->payment_status, ['verified', 'paid', 'failed'])) {
            return back()->with('error', 'No payment screenshot to reject or already processed.');
        }

        $order->update([
            'payment_status' => 'failed',
            'payment_note' => $request->note,
            'payment_verified_at' => now(),
            'payment_verified_by' => auth('staff')->id(),
            'status' => 'cancelled',
        ]);

        return redirect()->route('staff.orders.view-screenshot', $order->id)
            ->with('error', 'Payment rejected.');
    }

    public function viewScreenshot(Order $order)
    {
        $this->authorize('orders.manage');
        return view('staff.orders.screenshot', compact('order'));
    }

    public function viewScreenshotRaw(Order $order)
    {
        $this->authorize('orders.manage');
        
        if (! $order->payment_screenshot) {
            abort(404);
        }

        $path = storage_path('app/public/'.$order->payment_screenshot);

        if (! file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function outForDelivery(Order $order)
    {
        $this->authorize('orders.manage');

        // Security: Ensure order is COD and can start delivery
        if (! $order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as out for delivery.');
        }

        if (! $order->canStartDelivery()) {
            return back()->with('error', 'Order cannot be marked as out for delivery. It must be ready first.');
        }

        $order->markAsOutForDelivery();

        return back()->with('success', 'Order is now out for delivery.');
    }

    public function markDelivered(Order $order)
    {
        $this->authorize('orders.manage');

        // Security: Ensure order is COD and can collect cash
        if (! $order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as delivered.');
        }

        if (! $order->canCollectCash()) {
            return back()->with('error', 'Order cannot be marked as delivered. It must be out for delivery first.');
        }

        $order->markAsDelivered();

        // Update order status to confirmed
        $order->update(['status' => 'confirmed']);

        return back()->with('success', 'Cash collected! Order marked as delivered.');
    }

    public function markDeliveryFailed(Request $request, Order $order)
    {
        $this->authorize('orders.manage');

        // Security: Validate request
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Security: Ensure order is COD
        if (! $order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as failed.');
        }

        if ($order->delivery_status === Order::DELIVERY_STATUS_DELIVERED) {
            return back()->with('error', 'Order is already delivered.');
        }

        $order->markAsFailed($request->reason);

        return back()->with('error', 'Delivery marked as failed.');
    }
}
