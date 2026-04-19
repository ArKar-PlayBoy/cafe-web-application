<?php

namespace App\Observers;

use App\Models\StockItem;

class StockItemObserver
{
    public function updated(StockItem $stockItem): void
    {
        if ($stockItem->wasChanged(['current_quantity'])) {
            // Future: could trigger low stock alert creation here
        }
    }
}