<?php

namespace App\Models;

use App\Events\UserPermissionsChanged;
use App\Events\UserRoleChanged;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

/**
 * @property int|null $role_id
 * @property Role|null $role
 * @property string $name
 * @property string $email
 */
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role && (in_array($this->role->slug, ['admin', 'super_admin']) || $this->role->is_super_admin);
    }

    public function isStaff(): bool
    {
        return $this->role && ($this->role->slug === 'staff' || $this->isAdmin());
    }

    /**
     * Check if user has super admin role
     */
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

        // Get all permissions (cached with single key)
        $allPermissions = $this->getAllPermissions();

        return in_array($permission, $allPermissions);
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
            if (! $this->hasPermission($permission)) {
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
        if ($targetUser->isAdmin() && ! $this->isSuperAdmin()) {
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
        if ($targetUser->isAdmin() && ! $this->isSuperAdmin()) {
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
    }

    public function assignRole(Role $role): void
    {
        $oldRoleId = $this->role_id;
        $this->role_id = $role->id;
        $this->save();

        Event::dispatch(new UserRoleChanged($this, $oldRoleId));
    }

    public function syncDirectPermissions(array $permissionIds): void
    {
        $this->directPermissions()->sync($permissionIds);

        Event::dispatch(new UserPermissionsChanged($this));
    }

    public function attachPermission(Permission $permission): void
    {
        $this->directPermissions()->attach($permission->id);

        Event::dispatch(new UserPermissionsChanged($this));
    }

    public function detachPermission(Permission $permission): void
    {
        $this->directPermissions()->detach($permission->id);

        Event::dispatch(new UserPermissionsChanged($this));
    }

    public function getAllPermissions(): array
    {
        $cacheKey = "user:{$this->id}:permissions";

        return Cache::remember($cacheKey, 300, function () {
            $permissions = collect();

            if ($this->role) {
                // Ensure role permissions are loaded
                if (! $this->role->relationLoaded('permissions')) {
                    $this->role->load('permissions');
                }
                $permissions = $permissions->merge($this->role->permissions->pluck('slug'));
            }

            // Ensure direct permissions are loaded
            if (! $this->relationLoaded('directPermissions')) {
                $this->load('directPermissions');
            }
            $permissions = $permissions->merge($this->directPermissions->pluck('slug'));

            return $permissions->unique()->values()->toArray();
        });
    }

    public function updateOrderStats(float $orderTotal): void
    {
        $this->total_orders = ($this->total_orders ?? 0) + 1;
        $this->total_spent = ($this->total_spent ?? 0) + $orderTotal;

        $today = now()->toDateString();

        if (! $this->first_order_date) {
            $this->first_order_date = $today;
        }
        $this->last_order_date = $today;

        $this->save();
    }
}
