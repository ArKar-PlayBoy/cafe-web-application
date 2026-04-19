<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\CacheService;

class OrderObserver
{
    public function created(Order $order): void
    {
        CacheService::forget(CacheService::report('sales', now()->toDateString(), now()->toDateString()));
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged(['payment_status', 'status'])) {
            CacheService::forget(CacheService::report('sales', now()->toDateString(), now()->toDateString()));
        }
    }

    public function deleted(Order $order): void
    {
        CacheService::forget(CacheService::report('sales', now()->toDateString(), now()->toDateString()));
    }
}