<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'group',
        'description',
        'is_critical',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permission');
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }

    public function scopeByGroup($query, $group)
    {
        return $query->where('group', $group);
    }
}
