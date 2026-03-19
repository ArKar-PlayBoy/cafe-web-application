<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Mail\OrderStatusUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdate implements ShouldQueue
{
    public function handle(OrderStatusChanged $event): void
    {
        $order = $event->order;

        if (! $order->user) {
            Log::warning('Order status email skipped: Order has no user', [
                'order_id' => $order->id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);

            return;
        }

        if (! $order->user->email) {
            Log::warning('Order status email skipped: User has no email', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);

            return;
        }

        if (! filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Order status email skipped: Invalid email format', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'email' => $order->user->email,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);

            return;
        }

        try {
            Mail::to($order->user->email)->send(
                new OrderStatusUpdated($order, $event->oldStatus, $event->newStatus)
            );

            Log::info('Order status update email sent', [
                'order_id' => $order->id,
                'email' => $order->user->email,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status update email', [
                'order_id' => $order->id,
                'email' => $order->user->email,
                'old_status' => $event->oldStatus,
                'new_status' => $event->newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
