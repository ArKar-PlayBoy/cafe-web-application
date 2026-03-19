<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('items.menuItem', 'rejection')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        $order->load('items.menuItem', 'user', 'rejection', 'canceller');

        return view('customer.orders.show', compact('order'));
    }

    public function uploadPayment(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($order->payment_status, ['verified', 'paid'])) {
            return back()->with('error', 'Payment already verified.');
        }

        if ($order->payment_status === 'awaiting_verification' && $order->payment_screenshot) {
            return back()->with('error', 'Payment screenshot already uploaded. Please wait for verification.');
        }

        if ($order->payment_method === 'cod') {
            return back()->with('error', 'COD orders do not require payment upload.');
        }

        $request->validate([
            'reference' => 'nullable|string|max:255',
        ]);

        $screenshotPath = null;
        if ($request->hasFile('screenshot')) {
            $screenshot = $request->file('screenshot');
            // Use UUID-based filename to prevent enumeration and path guessing
            $extension = $screenshot->getClientOriginalExtension() ?: 'jpg';
            $filename = \Illuminate\Support\Str::uuid().'.'.strtolower($extension);
            $path = 'payments/'.$order->id;
            $screenshotPath = $screenshot->storeAs($path, $filename, 'public');
        }

        $order->update([
            'payment_screenshot' => $screenshotPath,
            'payment_reference' => $request->reference,
            'payment_status' => 'awaiting_verification',
        ]);

        \Illuminate\Support\Facades\Log::info('Payment screenshot uploaded', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'payment_status' => 'awaiting_verification',
        ]);

        return back()->with('success', 'Payment screenshot uploaded successfully. Please wait for verification.');
    }

    public function cancel(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        if ($order->delivery_status === Order::DELIVERY_STATUS_OUT_FOR_DELIVERY) {
            return back()->with('error', 'Order is already out for delivery and cannot be cancelled. Please contact support.');
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_by' => auth()->id(),
        ]);

        return redirect()->route('orders')->with('success', 'Order cancelled successfully.');
    }

    public function viewScreenshot(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }

        if (! $order->payment_screenshot) {
            abort(404);
        }

        // Sanitize path to prevent directory traversal
        $filename = basename($order->payment_screenshot);
        $directory = dirname($order->payment_screenshot);
        // Only allow paths within the expected storage directory
        $safePath = storage_path('app/public/'.$directory.'/'.$filename);
        $realPath = realpath($safePath);
        $allowedBase = realpath(storage_path('app/public'));

        if (! $realPath || ! $allowedBase || ! str_starts_with($realPath, $allowedBase)) {
            abort(404);
        }

        return response()->file($realPath);
    }
}
