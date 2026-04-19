<?php

namespace App\Jobs;

use App\Models\StockAlert;
use App\Models\StockItem;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckLowStockAlert implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lowStockItems = StockItem::whereColumn('current_quantity', '<', 'min_quantity')->get();
        $created = 0;

        foreach ($lowStockItems as $item) {
            $exists = StockAlert::where('stock_item_id', $item->id)
                ->where('type', 'low_stock')
                ->where('is_read', false)
                ->exists();

            if (! $exists) {
                StockAlert::create([
                    'stock_item_id' => $item->id,
                    'type' => 'low_stock',
                    'is_read' => false,
                ]);
                $created++;
            }
        }

        Log::info('CheckLowStockAlert job completed', [
            'items_checked' => $lowStockItems->count(),
            'alerts_created' => $created,
        ]);
    }
}