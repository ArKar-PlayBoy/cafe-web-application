<?php

use App\Models\StockAlert;
use App\Models\StockItem;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('stock:seed-alerts', function () {
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

    $this->info("Created {$created} low stock alerts for {$lowStockItems->count()} low stock items.");
})->purpose('Seed low stock alerts for existing items');
