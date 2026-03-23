<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id', 'name', 'description', 'price', 'featured_image', 'is_available'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function stockItems(): BelongsToMany
    {
        return $this->belongsToMany(StockItem::class, 'menu_item_stock')
            ->withPivot('quantity_needed')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeTrashed($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)->whereNull('deleted_at');
    }

    public function canBeSafelyDeleted(): bool
    {
        return $this->orderItems()->count() === 0 && $this->cartItems()->count() === 0;
    }

    public function hasActiveOrders(): bool
    {
        return $this->orderItems()->whereHas('order', function ($q) {
            $q->whereIn('status', ['pending', 'preparing', 'ready']);
        })->exists();
    }

    public function getOrderCount(): int
    {
        return $this->orderItems()->count();
    }

    public function getIngredientCost(): float
    {
        $cost = 0;

        foreach ($this->stockItems as $stockItem) {
            if ($stockItem->unit_cost && $stockItem->pivot->quantity_needed) {
                $cost += $stockItem->unit_cost * $stockItem->pivot->quantity_needed;
            }
        }

        return round($cost, 2);
    }

    public function getProfit(): float
    {
        return round($this->price - $this->getIngredientCost(), 2);
    }

    public function getProfitMargin(): float
    {
        if ($this->price <= 0) {
            return 0;
        }

        return round(($this->getProfit() / $this->price) * 100, 1);
    }

    public function getMarginClass(): string
    {
        $margin = $this->getProfitMargin();

        if ($margin >= 50) {
            return 'text-emerald-600 dark:text-emerald-400';
        } elseif ($margin >= 20) {
            return 'text-amber-600 dark:text-amber-400';
        } else {
            return 'text-rose-600 dark:text-rose-400';
        }
    }

    public function getMarginBgClass(): string
    {
        $margin = $this->getProfitMargin();

        if ($margin >= 50) {
            return 'bg-emerald-100 dark:bg-emerald-500/20';
        } elseif ($margin >= 20) {
            return 'bg-amber-100 dark:bg-amber-500/20';
        } else {
            return 'bg-rose-100 dark:bg-rose-500/20';
        }
    }
}
