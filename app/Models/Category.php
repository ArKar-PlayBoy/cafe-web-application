<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'slug'];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function activeMenuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('deleted_at');
    }

    public function hasMenuItems(): bool
    {
        return $this->menuItems()->count() > 0;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeTrashed($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function canBeSafelyDeleted(): bool
    {
        return !$this->hasMenuItems();
    }

    public function getMenuItemCount(): int
    {
        return $this->menuItems()->count();
    }
}
