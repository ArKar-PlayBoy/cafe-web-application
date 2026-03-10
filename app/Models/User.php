<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'stripe_customer_id',
        'role_id',
        'total_orders',
        'total_spent',
        'first_order_date',
        'last_order_date',
    ];

    protected $guarded = [
        'role_id',
        'is_banned',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'total_spent' => 'decimal:2',
            'first_order_date' => 'date',
            'last_order_date' => 'date',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function approvalRequests(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'requested_by');
    }

    public function approvalsGiven(): HasMany
    {
        return $this->hasMany(ApprovalRequest::class, 'approved_by');
    }

    // Role checking methods using slugs (stable, lowercase identifiers)
    public function isAdmin(): bool
    {
        return $this->role && (in_array($this->role->slug, ['admin', 'super_admin']) || $this->role->is_super_admin);
    }

    public function isStaff(): bool
    {
        return $this->role && ($this->role->slug === 'staff' || $this->isAdmin());
    }

    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->is_super_admin;
    }

    // Permission-based methods
    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check cache first
        $cacheKey = "user:{$this->id}:permission:{$permission}";
        return Cache::remember($cacheKey, 300, function () use ($permission) {
            // Check direct permissions
            if ($this->directPermissions()->where('slug', $permission)->exists()) {
                return true;
            }

            // Check role permissions
            if ($this->role && $this->role->hasPermission($permission)) {
                return true;
            }

            return false;
        });
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    public function canPerformCriticalAction(string $action): bool
    {
        return $this->isSuperAdmin();
    }

    public function canBanUser(User $targetUser): bool
    {
        // Super admins cannot be banned
        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        // Only super admins can ban admins
        if ($targetUser->isAdmin() && !$this->isSuperAdmin()) {
            return false;
        }

        // Cannot ban yourself
        if ($this->id === $targetUser->id) {
            return false;
        }

        return $this->hasPermission('users.ban');
    }

    public function canDeleteUser(User $targetUser): bool
    {
        // Cannot delete yourself
        if ($this->id === $targetUser->id) {
            return false;
        }

        // Only super admins can delete other admins
        if ($targetUser->isAdmin() && !$this->isSuperAdmin()) {
            return false;
        }

        // Super admins cannot be deleted
        if ($targetUser->isSuperAdmin()) {
            return false;
        }

        return $this->hasPermission('users.manage_staff') || $this->hasPermission('users.manage_customers');
    }

    public function canDeleteCategory(): bool
    {
        return $this->isSuperAdmin() && $this->hasPermission('categories.delete');
    }

    public function canManagePermissions(): bool
    {
        return $this->isSuperAdmin() && $this->hasPermission('system.manage_permissions');
    }

    // Ban methods
    public function isBanned(): bool
    {
        return (bool) $this->is_banned;
    }

    public function ban(?string $reason = null): void
    {
        $this->is_banned = true;
        $this->banned_at = now();
        $this->ban_reason = $reason;
        $this->save();
    }

    public function unban(): void
    {
        $this->is_banned = false;
        $this->banned_at = null;
        $this->ban_reason = null;
        $this->save();
    }

    // Cache clearing
    public function clearPermissionCache(): void
    {
        Cache::forget("user:{$this->id}:permissions");
        Cache::forget("user:{$this->id}:all_permissions");
        Cache::forget("user:{$this->id}:permission:users.edit");
        Cache::forget("user:{$this->id}:permission:users.delete");
        Cache::forget("user:{$this->id}:permission:users.ban");
        Cache::forget("user:{$this->id}:permission:users.create");
        Cache::forget("user:{$this->id}:permission:users.view");
        Cache::forget("user:{$this->id}:permission:users.manage_staff");
    }

    public function getAllPermissions(): array
    {
        $cacheKey = "user:{$this->id}:permissions";
        return Cache::remember($cacheKey, 300, function () {
            $permissions = collect();

            if ($this->role) {
                $permissions = $permissions->merge($this->role->permissions()->pluck('slug'));
            }

            $permissions = $permissions->merge($this->directPermissions()->pluck('slug'));

            return $permissions->unique()->values()->toArray();
        });
    }

    public function updateOrderStats(float $orderTotal): void
    {
        $this->total_orders = ($this->total_orders ?? 0) + 1;
        $this->total_spent = ($this->total_spent ?? 0) + $orderTotal;
        
        $today = now()->toDateString();
        
        if (!$this->first_order_date) {
            $this->first_order_date = $today;
        }
        $this->last_order_date = $today;
        
        $this->save();
    }
}
