<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'is_super_admin'];

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

    public function hasPermission(string $permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->permissions()->where('slug', $permission)->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }

        return $this->permissions()->whereIn('slug', $permissions)->exists();
    }

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
