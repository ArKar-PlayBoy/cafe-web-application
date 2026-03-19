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
