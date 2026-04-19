<?php

namespace App\Http\Controllers\Admin;

use App\Events\OrderCreated;
use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $this->authorize('orders.view');

        // Show orders that either:
        // 1. Are COD (payment collected on delivery)
        // 2. Are Stripe orders with verified/paid payment
        // 3. Have verified/paid payment status
        // 4. Have a payment screenshot uploaded (awaiting verification)
        // 5. Are KBZ Pay orders waiting for payment (payment_status = awaiting_verification)
        $orders = Order::with('user', 'items.menuItem')
            ->where(function ($query) {
                $query->where('payment_method', 'cod')
                    ->orWhere(function ($q) {
                        $q->where('payment_method', 'stripe')
                          ->whereIn('payment_status', ['verified', 'paid']);
                    })
                    ->orWhereIn('payment_status', ['verified', 'paid'])
                    ->orWhereNotNull('payment_screenshot')
                    ->orWhere(function ($q) {
                        $q->where('payment_method', 'kbz_pay')
                            ->where('payment_status', 'awaiting_verification');
                    });
            })
            ->latest()
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    public function verifyPayment(Order $order)
    {
        $this->authorize('orders.verify_payment');
        if (! $order->canReviewPayment()) {
            return back()->with('error', 'Payment cannot be reviewed.');
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

        // Create kitchen ticket after payment verified (idempotent)
        KitchenTicket::firstOrCreate([
            'order_id' => $order->id,
        ], [
            'status' => 'new',
        ]);

        // Dispatch order confirmation email - KBZ Pay payment now verified
        event(new OrderCreated($order));

        return redirect()->route('admin.orders.view-screenshot', $order->id)
            ->with('success', 'Payment verified successfully.');
    }

    public function rejectPayment(Request $request, Order $order)
    {
        $this->authorize('orders.verify_payment');
        $request->validate([
            'note' => 'required|string|max:1000',
        ]);

        if (! $order->canReviewPayment()) {
            return back()->with('error', 'Payment cannot be reviewed.');
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

        if (! $order->payment_screenshot) {
            abort(404, 'No payment screenshot uploaded for this order.');
        }

        $realPath = $this->resolvePaymentScreenshotPath($order->payment_screenshot);

        if (! $realPath) {
            abort(404);
        }

        return response()->file($realPath);
    }

    private function resolvePaymentScreenshotPath(string $storedPath): ?string
    {
        $filename = basename($storedPath);
        $directory = trim(dirname($storedPath), '.\\/') ?: '';
        $privateCandidate = storage_path('app/private/'.($directory ? $directory.'/' : '').$filename);
        $legacyPublicCandidate = storage_path('app/public/'.($directory ? $directory.'/' : '').$filename);

        $allowedBases = array_filter([
            realpath(storage_path('app/private')),
            realpath(storage_path('app/public')),
        ]);

        foreach ([$privateCandidate, $legacyPublicCandidate] as $candidate) {
            $realPath = realpath($candidate);
            if (! $realPath) {
                continue;
            }

            foreach ($allowedBases as $allowedBase) {
                if (str_starts_with($realPath, $allowedBase)) {
                    return $realPath;
                }
            }
        }

        return null;
    }

    public function outForDelivery(Order $order)
    {
        $this->authorize('orders.manage');

        if (! $order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as out for delivery.');
        }

        if (! $order->canStartDelivery()) {
            return back()->with('error', 'Order cannot be marked as out for delivery.');
        }

        $order->markAsOutForDelivery();

        return back()->with('success', 'Order is now out for delivery.');
    }

    public function markDelivered(Order $order)
    {
        $this->authorize('orders.verify_payment');

        if (! $order->isCOD()) {
            return back()->with('error', 'Only COD orders can be marked as delivered.');
        }

        if (! $order->canCollectCash()) {
            return back()->with('error', 'Order cannot be marked as delivered.');
        }

        $order->markAsDelivered();
        $order->update(['status' => 'confirmed']);

        return back()->with('success', 'Cash collected! Order marked as delivered.');
    }

    public function markDeliveryFailed(Request $request, Order $order)
    {
        $this->authorize('orders.manage');

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

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
