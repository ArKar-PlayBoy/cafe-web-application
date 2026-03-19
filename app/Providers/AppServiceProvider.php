<?php

namespace App\Providers;

use App\Events\OrderCreated;
use App\Events\OrderStatusChanged;
use App\Events\RolePermissionsUpdated;
use App\Events\UserPermissionsChanged;
use App\Events\UserRoleChanged;
use App\Listeners\ClearRoleUserPermissionsCache;
use App\Listeners\ClearUserAndOldRolePermissionsCache;
use App\Listeners\ClearUserPermissionsCache;
use App\Listeners\SendOrderConfirmation;
use App\Listeners\SendOrderStatusUpdate;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            OrderCreated::class,
            SendOrderConfirmation::class
        );

        Event::listen(
            OrderStatusChanged::class,
            SendOrderStatusUpdate::class
        );

        Event::listen(
            RolePermissionsUpdated::class,
            ClearRoleUserPermissionsCache::class
        );

        Event::listen(
            UserPermissionsChanged::class,
            ClearUserPermissionsCache::class
        );

        Event::listen(
            UserRoleChanged::class,
            ClearUserAndOldRolePermissionsCache::class
        );

        // Register Gates for all permissions
        $this->registerPermissionGates();

        // Register model-specific Gates
        $this->registerModelGates();
    }

    /**
     * Register Gates for all permissions defined in PermissionService.
     */
    protected function registerPermissionGates(): void
    {
        foreach (PermissionService::PERMISSION_GROUPS as $group => $data) {
            foreach ($data['permissions'] as $slug => $name) {
                Gate::define($slug, function ($user) use ($slug) {
                    if (! $user) {
                        return false;
                    }

                    return $user->hasPermission($slug);
                });
            }
        }
    }

    /**
     * Register model-specific Gates for complex authorization logic.
     */
    protected function registerModelGates(): void
    {
        // Super Admin can do anything
        Gate::before(function (User $user) {
            if ($user->isSuperAdmin()) {
                return true;
            }
        });

        // Delete Category Gate
        Gate::define('delete-category', function (User $user, Category $category) {
            if (! $user->isSuperAdmin()) {
                return false;
            }

            if (! $user->hasPermission('categories.delete')) {
                return false;
            }

            return true;
        });

        // Delete Menu Item Gate
        Gate::define('delete-menu-item', function (User $user, MenuItem $menuItem) {
            if (! $user->hasPermission('menu.delete')) {
                return false;
            }

            // Check if menu item has active orders
            if ($menuItem->hasActiveOrders()) {
                return false;
            }

            return true;
        });

        // Ban User Gate
        Gate::define('ban-user', function (User $admin, User $target) {
            $result = PermissionService::canBanUser($admin, $target);

            return $result['allowed'];
        });

        // Delete User Gate
        Gate::define('delete-user', function (User $admin, User $target) {
            $result = PermissionService::canDeleteUser($admin, $target);

            return $result['allowed'];
        });

        // Manage Permissions Gate
        Gate::define('manage-permissions', function (User $user) {
            return $user->isSuperAdmin() && $user->hasPermission('system.manage_permissions');
        });

        // View Audit Logs Gate
        Gate::define('view-audit-logs', function (User $user) {
            return $user->isSuperAdmin() && $user->hasPermission('system.view_logs');
        });

        // Approve Critical Actions Gate
        Gate::define('approve-critical', function (User $user) {
            return $user->isSuperAdmin() && $user->hasPermission('system.approve_critical');
        });
    }
}
