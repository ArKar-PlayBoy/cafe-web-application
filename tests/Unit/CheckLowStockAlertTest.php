<?php

namespace Tests\Unit;

use App\Jobs\CheckLowStockAlert;
use App\Models\StockAlert;
use App\Models\StockItem;
use Illuminate\Support\Facades\DB;
use Pest\BeforeEach;

beforeEach(function () {
    $this->stockItem = StockItem::create([
        'name' => 'Test Item',
        'current_quantity' => 5,
        'min_quantity' => 10,
        'unit' => 'pcs',
    ]);

    DB::table('stock_alerts')->where('stock_item_id', $this->stockItem->id)->delete();
});

afterEach(function () {
    $this->stockItem?->delete();
});

it('creates low stock alert for items below minimum', function () {
    $job = new CheckLowStockAlert();
    $job->handle();

    $this->assertDatabaseHas('stock_alerts', [
        'stock_item_id' => $this->stockItem->id,
        'type' => 'low_stock',
        'is_read' => false,
    ]);
});

it('does not create duplicate alerts', function () {
    StockAlert::create([
        'stock_item_id' => $this->stockItem->id,
        'type' => 'low_stock',
        'is_read' => false,
    ]);

    $job = new CheckLowStockAlert();
    $job->handle();

    $this->assertDatabaseCount('stock_alerts', 1);
});