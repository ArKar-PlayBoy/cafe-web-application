<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Services\PermissionService;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin - has all permissions including critical ones
        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full system access with all permissions including critical operations',
                'is_super_admin' => true,
            ]
        );

        // Admin - has most permissions but not critical ones
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrative access without critical system operations',
                'is_super_admin' => false,
            ]
        );

        // Staff - limited access
        $staff = Role::updateOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'Basic staff access for order and reservation management',
                'is_super_admin' => false,
            ]
        );

        // Customer role (no permissions, just for identification)
        Role::updateOrCreate(
            ['slug' => 'customer'],
            [
                'name' => 'Customer',
                'description' => 'Regular customer account',
                'is_super_admin' => false,
            ]
        );

        // Now assign permissions
        // Super Admin gets all permissions automatically (is_super_admin = true)
        // But we'll assign them anyway for clarity
        $allPermissions = [];
        foreach (PermissionService::PERMISSION_GROUPS as $group => $data) {
            $allPermissions = array_merge($allPermissions, array_keys($data['permissions']));
        }
        PermissionService::assignPermissionsToRole($superAdmin, $allPermissions);

        // Admin permissions (all except critical)
        $adminPermissions = [
            // Orders
            'orders.view', 'orders.manage', 'orders.cancel', 'orders.verify_payment',
            // Reservations
            'reservations.view', 'reservations.manage',
            // Stock
            'stock.view', 'stock.manage', 'stock.adjust', 'stock.waste', 'stock.delete',
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.manage_staff', 'users.manage_customers', 'users.ban',
            // Categories
            'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
            // Menu
            'menu.view', 'menu.create', 'menu.edit', 'menu.delete',
            // Tables
            'tables.view', 'tables.create', 'tables.edit', 'tables.delete',
        ];
        PermissionService::assignPermissionsToRole($admin, $adminPermissions);

        // Staff permissions
        $staffPermissions = [
            // Orders
            'orders.view', 'orders.manage', 'orders.cancel', 'orders.verify_payment',
            // Reservations
            'reservations.view', 'reservations.manage',
            // Stock (view only)
            'stock.view',
            // Users (view only)
            'users.view',
            // Categories (view only)
            'categories.view',
            // Menu (view only)
            'menu.view',
            // Tables (view only)
            'tables.view',
        ];
        PermissionService::assignPermissionsToRole($staff, $staffPermissions);
    }
}
