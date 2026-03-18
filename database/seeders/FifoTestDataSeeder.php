<?php

namespace Database\Seeders;

use App\Models\MenuItem;
use App\Models\StockBatch;
use App\Models\StockItem;
use Illuminate\Database\Seeder;

class FifoTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $coffee = StockItem::where('name', 'Coffee Beans')->first();
        $milk = StockItem::where('name', 'Milk')->first();

        if ($coffee) {
            $coffee->current_quantity = 0;
            $coffee->save();

            StockBatch::create([
                'stock_item_id' => $coffee->id,
                'quantity' => 500,
                'cost' => 25.00,
                'received_date' => now()->subDays(10),
                'expiry_date' => now()->addMonths(6),
            ]);

            StockBatch::create([
                'stock_item_id' => $coffee->id,
                'quantity' => 500,
                'cost' => 28.00,
                'received_date' => now()->subDays(5),
                'expiry_date' => now()->addMonths(6),
            ]);

            StockBatch::create([
                'stock_item_id' => $coffee->id,
                'quantity' => 500,
                'cost' => 30.00,
                'received_date' => now()->subDays(1),
                'expiry_date' => now()->addMonths(6),
            ]);

            $coffee->current_quantity = 1500;
            $coffee->save();
        }

        if ($milk) {
            $milk->current_quantity = 0;
            $milk->save();

            StockBatch::create([
                'stock_item_id' => $milk->id,
                'quantity' => 10,
                'cost' => 3.50,
                'received_date' => now()->subDays(8),
                'expiry_date' => now()->addDays(2),
            ]);

            StockBatch::create([
                'stock_item_id' => $milk->id,
                'quantity' => 10,
                'cost' => 3.80,
                'received_date' => now()->subDays(3),
                'expiry_date' => now()->addDays(7),
            ]);

            StockBatch::create([
                'stock_item_id' => $milk->id,
                'quantity' => 10,
                'cost' => 4.00,
                'received_date' => now(),
                'expiry_date' => now()->addDays(10),
            ]);

            $milk->current_quantity = 30;
            $milk->save();
        }

        $latte = MenuItem::where('name', 'like', '%Latte%')->first();
        if ($latte && $coffee && $milk) {
            $latte->stockItems()->sync([
                $coffee->id => ['quantity_needed' => 20],
                $milk->id => ['quantity_needed' => 1],
            ]);
        }

        $this->command->info('FIFO test data created!');
        $this->command->info('- Coffee Beans: 3 batches (oldest 10 days, newest 1 day)');
        $this->command->info('- Milk: 3 batches (expiring soon)');
    }
}
