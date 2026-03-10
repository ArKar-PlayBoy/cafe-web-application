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
}
