<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmation implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        if (! $order->user) {
            Log::warning('Order confirmation email skipped: Order has no user', [
                'order_id' => $order->id,
            ]);

            return;
        }

        if (! $order->user->email) {
            Log::warning('Order confirmation email skipped: User has no email', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
            ]);

            return;
        }

        if (! filter_var($order->user->email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Order confirmation email skipped: Invalid email format', [
                'order_id' => $order->id,
                'user_id' => $order->user_id,
                'email' => $order->user->email,
            ]);

            return;
        }

        try {
            Mail::to($order->user->email)->send(new OrderConfirmed($order));

            Log::info('Order confirmation email sent', [
                'order_id' => $order->id,
                'email' => $order->user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order confirmation email', [
                'order_id' => $order->id,
                'email' => $order->user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
