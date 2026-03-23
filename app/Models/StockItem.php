<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockItem extends Model
{
    protected $fillable = [
        'name',
        'current_quantity',
        'min_quantity',
        'barcode',
        'bin_location',
        'category',
        'unit',
        'unit_cost',
    ];

    protected $casts = [
        'current_quantity' => 'integer',
        'min_quantity' => 'integer',
        'unit_cost' => 'decimal:2',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(StockBatch::class)->orderBy('received_date', 'asc');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(StockAlert::class);
    }

    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_stock')
            ->withPivot('quantity_needed');
    }

    public function wasteLogs(): HasMany
    {
        return $this->hasMany(WasteLog::class);
    }

    public function getActiveBatches()
    {
        return $this->batches()->where('quantity', '>', 0)->get();
    }

    public function isLowStock(): bool
    {
        return $this->current_quantity < $this->min_quantity;
    }

    public function getFormattedCost(): string
    {
        if (! $this->unit_cost) {
            return 'N/A';
        }

        return '$' . number_format($this->unit_cost, 2) . '/' . $this->unit;
    }

    public function getAverageBatchCost(): ?float
    {
        $batches = $this->batches()->where('quantity', '>', 0)->get();

        if ($batches->isEmpty()) {
            return null;
        }

        $totalCost = $batches->sum(fn ($batch) => $batch->quantity * $batch->cost);
        $totalQty = $batches->sum('quantity');

        return $totalQty > 0 ? $totalCost / $totalQty : null;
    }
}
