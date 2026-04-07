<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property bool $is_super_admin
 */
class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description'];

    protected $casts = [
        'is_super_admin' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get the customer role (cached)
     */
    public static function lookupCustomerRole(): ?Role
    {
        return static::getCached('customer');
    }

    /**
     * Get a role by slug with caching
     */
    private static function getCached(string $slug): ?Role
    {
        $cacheKey = "role:{$slug}";
        
        return cache()->remember($cacheKey, 3600, function () use ($slug) {
            return static::where('slug', $slug)->first();
        });
    }

    /**
     * Clear role cache (useful for testing)
     */
    public static function clearCache(string $slug = null): void
    {
        if ($slug) {
            cache()->forget("role:{$slug}");
        } else {
            // Clear all role caches
            foreach (static::pluck('slug') as $roleSlug) {
                cache()->forget("role:{$roleSlug}");
            }
        }
    }

    /**
     * Check if role is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    public function scopeSuperAdmin($query)
    {
        return $query->where('is_super_admin', true);
    }

    public function scopeRegular($query)
    {
        return $query->where('is_super_admin', false);
    }
}
