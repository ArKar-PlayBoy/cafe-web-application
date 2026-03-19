<?php

namespace App\Services;

use App\Events\RolePermissionsUpdated;
use App\Models\ApprovalRequest;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;

class PermissionService
{
    public const PERMISSION_GROUPS = [
        'orders' => [
            'label' => 'Order Management',
            'permissions' => [
                'orders.view' => 'View Orders',
                'orders.manage' => 'Manage Orders',
                'orders.cancel' => 'Cancel Orders',
                'orders.verify_payment' => 'Verify Payments',
            ],
        ],
        'reservations' => [
            'label' => 'Reservation Management',
            'permissions' => [
                'reservations.view' => 'View Reservations',
                'reservations.manage' => 'Manage Reservations',
            ],
        ],
        'stock' => [
            'label' => 'Stock Management',
            'permissions' => [
                'stock.view' => 'View Stock',
                'stock.manage' => 'Manage Stock (Create/Edit)',
                'stock.adjust' => 'Adjust Stock',
                'stock.waste' => 'Log Waste',
                'stock.delete' => 'Delete Stock Items',
            ],
        ],
        'users' => [
            'label' => 'User Management',
            'permissions' => [
                'users.view' => 'View Users',
                'users.create' => 'Create Users',
                'users.edit' => 'Edit Users',
                'users.delete' => 'Delete Users',
                'users.manage_staff' => 'Manage Staff',
                'users.manage_customers' => 'Manage Customers',
                'users.ban' => 'Ban/Unban Users',
            ],
        ],
        'categories' => [
            'label' => 'Category Management',
            'permissions' => [
                'categories.view' => 'View Categories',
                'categories.create' => 'Create Categories',
                'categories.edit' => 'Edit Categories',
                'categories.delete' => 'Delete Categories (Critical)',
            ],
        ],
        'menu' => [
            'label' => 'Menu Management',
            'permissions' => [
                'menu.view' => 'View Menu Items',
                'menu.create' => 'Create Menu Items',
                'menu.edit' => 'Edit Menu Items',
                'menu.delete' => 'Delete Menu Items',
            ],
        ],
        'system' => [
            'label' => 'System Management (Super Admin Only)',
            'permissions' => [
                'system.delete_admins' => 'Delete Admin Users',
                'system.manage_permissions' => 'Manage Permissions',
                'system.view_logs' => 'View Audit Logs',
                'system.approve_critical' => 'Approve Critical Actions',
            ],
        ],
        'tables' => [
            'label' => 'Table Management',
            'permissions' => [
                'tables.view' => 'View Tables',
                'tables.create' => 'Create Tables',
                'tables.edit' => 'Edit Tables',
                'tables.delete' => 'Delete Tables',
            ],
        ],
    ];

    public const CRITICAL_PERMISSIONS = [
        'categories.delete',
        'system.delete_admins',
        'system.manage_permissions',
    ];

    public static function initializePermissions(): void
    {
        foreach (self::PERMISSION_GROUPS as $group => $data) {
            foreach ($data['permissions'] as $slug => $name) {
                Permission::firstOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => $name,
                        'group' => $group,
                        'description' => $data['label'].' - '.$name,
                        'is_critical' => in_array($slug, self::CRITICAL_PERMISSIONS),
                    ]
                );
            }
        }
    }

    public static function assignPermissionsToRole(Role $role, array $permissionSlugs): void
    {
        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $role->permissions()->sync($permissionIds);

        // Dispatch event to clear permission cache for all users with this role
        Event::dispatch(new RolePermissionsUpdated($role));
    }

    public static function createRoleWithPermissions(string $name, string $slug, array $permissionSlugs, ?string $description = null): Role
    {
        $role = Role::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'is_super_admin' => false,
            ]
        );

        self::assignPermissionsToRole($role, $permissionSlugs);

        return $role;
    }

    public static function requiresApproval(string $action, ?string $resourceType = null, ?int $resourceId = null): bool
    {
        $criticalActions = ['categories.delete', 'menu.delete', 'users.delete'];

        return in_array($action, $criticalActions);
    }

    public static function requestApproval(User $requester, string $action, string $resourceType, ?int $resourceId, array $payload = [], ?string $reason = null): ApprovalRequest
    {
        return ApprovalRequest::create([
            'requested_by' => $requester->id,
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'payload' => $payload,
            'reason' => $reason,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);
    }

    public static function getUserPermissions(User $user): array
    {
        return Cache::remember("user:{$user->id}:all_permissions", 300, function () use ($user) {
            return $user->getAllPermissions();
        });
    }

    public static function clearUserPermissionsCache(User $user): void
    {
        $user->clearPermissionCache();
        Cache::forget("user:{$user->id}:permissions");
    }

    public static function getAllPermissionsByGroup(): array
    {
        return self::PERMISSION_GROUPS;
    }

    public static function isCriticalPermission(string $permission): bool
    {
        return in_array($permission, self::CRITICAL_PERMISSIONS);
    }

    public static function canDeleteCategory(User $user, Category $category): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
            'requires_approval' => false,
        ];

        // Only super admins can delete categories
        if (! $user->isSuperAdmin()) {
            $result['reason'] = 'Only Super Admins can delete categories.';

            return $result;
        }

        if (! $user->hasPermission('categories.delete')) {
            $result['reason'] = 'You do not have permission to delete categories.';

            return $result;
        }

        // Check for dependencies
        if ($category->hasMenuItems()) {
            $result['requires_approval'] = true;
            $result['reason'] = "This category has {$category->getMenuItemCount()} menu items. Second admin approval required.";

            return $result;
        }

        $result['allowed'] = true;

        return $result;
    }

    public static function canBanUser(User $admin, User $target): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
        ];

        if (! $admin->hasPermission('users.ban')) {
            $result['reason'] = 'You do not have permission to ban users.';

            return $result;
        }

        if ($target->isSuperAdmin()) {
            $result['reason'] = 'Super Admins cannot be banned.';

            return $result;
        }

        if ($target->isAdmin() && ! $admin->isSuperAdmin()) {
            $result['reason'] = 'Only Super Admins can ban other admins.';

            return $result;
        }

        if ($admin->id === $target->id) {
            $result['reason'] = 'You cannot ban yourself.';

            return $result;
        }

        $result['allowed'] = true;

        return $result;
    }

    public static function canDeleteUser(User $admin, User $target): array
    {
        $result = [
            'allowed' => false,
            'reason' => '',
        ];

        if ($target->isSuperAdmin()) {
            $result['reason'] = 'Super Admin accounts cannot be deleted.';

            return $result;
        }

        if ($admin->id === $target->id) {
            $result['reason'] = 'You cannot delete your own account.';

            return $result;
        }

        if ($target->isAdmin() && ! $admin->isSuperAdmin()) {
            $result['reason'] = 'Only Super Admins can delete other admin accounts.';

            return $result;
        }

        $result['allowed'] = true;

        return $result;
    }
}
