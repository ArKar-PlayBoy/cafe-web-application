<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $this->authorize('orders.view');

        $orders = Order::with('user', 'items.menuItem')->latest()->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function verifyPayment(Order $order)
    {
        $this->authorize('orders.verify_payment');
        // Fix: Check for 'pending' or 'awaiting_verification' status - checkout sets 'pending', customer upload sets 'awaiting_verification'
        if (! $order->payment_screenshot || in_array($order->payment_status, ['verified', 'paid'])) {
            return back()->with('error', 'No payment screenshot to verify or already verified.');
        }

        $previousStatus = $order->payment_status;

        $order->update([
            'payment_status' => 'verified',
            'payment_verified_at' => now(),
            'payment_verified_by' => auth('admin')->id(),
            'status' => 'preparing',
        ]);

        \Illuminate\Support\Facades\Log::info('Payment verified by admin', [
            'order_id' => $order->id,
            'previous_status' => $previousStatus,
            'new_status' => 'verified',
            'admin_id' => auth('admin')->id(),
        ]);

        // Create kitchen ticket after payment verified
        KitchenTicket::create([
            'order_id' => $order->id,
            'status' => 'new',
        ]);

        return redirect()->route('admin.orders.view-screenshot', $order->id)
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
            'payment_verified_by' => auth('admin')->id(),
            'status' => 'cancelled',
        ]);

        return redirect()->route('admin.orders.view-screenshot', $order->id)
            ->with('error', 'Payment rejected.');
    }

    public function viewScreenshot(Order $order)
    {
        $this->authorize('orders.view');

        return view('admin.orders.screenshot', compact('order'));
    }

    public function viewScreenshotRaw(Order $order)
    {
        $this->authorize('orders.view');

        if (!$order->payment_screenshot) {
            abort(404, 'No payment screenshot uploaded for this order.');
        }

        $path = storage_path('app/public/' . $order->payment_screenshot);

        if (!file_exists($path)) {
            abort(404, 'Payment screenshot file not found on server.');
        }

        return response()->file($path);
    }

    public function outForDelivery(Order $order)
    {
        $this->authorize('orders.update');

        if (!$order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as out for delivery.');
        }

        if (!$order->canStartDelivery()) {
            return back()->with('error', 'Order cannot be marked as out for delivery.');
        }

        $order->markAsOutForDelivery();

        return back()->with('success', 'Order is now out for delivery.');
    }

    public function markDelivered(Order $order)
    {
        $this->authorize('orders.verify_payment');

        if (!$order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as delivered.');
        }

        if (!$order->canCollectCash()) {
            return back()->with('error', 'Order cannot be marked as delivered.');
        }

        $order->markAsDelivered();
        $order->update(['status' => 'confirmed']);

        return back()->with('success', 'Cash collected! Order marked as delivered.');
    }

    public function markDeliveryFailed(Request $request, Order $order)
    {
        $this->authorize('orders.update');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as failed.');
        }

        if ($order->delivery_status === Order::DELIVERY_STATUS_DELIVERED) {
            return back()->with('error', 'Order is already delivered.');
        }

        $order->markAsFailed($request->reason);

        return back()->with('error', 'Delivery marked as failed.');
    }
}
