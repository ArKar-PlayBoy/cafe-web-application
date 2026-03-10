<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PermissionController extends Controller
{
    public function index()
    {
        $this->authorize('system.manage_permissions');

        $roles = Role::with('permissions')->get();
        $permissions = Permission::orderBy('group')->orderBy('name')->get();
        $permissionGroups = PermissionService::getAllPermissionsByGroup();

        return view('admin.permissions.index', compact('roles', 'permissions', 'permissionGroups'));
    }

    public function editRole(Role $role)
    {
        $this->authorize('system.manage_permissions');

        // Cannot edit super admin role
        if ($role->is_super_admin) {
            return back()->with('error', 'Cannot modify Super Admin role permissions.');
        }

        $role->load('permissions');
        $permissionGroups = PermissionService::getAllPermissionsByGroup();
        $rolePermissions = $role->permissions->pluck('slug')->toArray();

        return view('admin.permissions.edit_role', compact('role', 'permissionGroups', 'rolePermissions'));
    }

    public function updateRole(Request $request, Role $role)
    {
        $this->authorize('system.manage_permissions');

        // Cannot edit super admin role
        if ($role->is_super_admin) {
            return back()->with('error', 'Cannot modify Super Admin role permissions.');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        $permissions = $request->input('permissions', []);
        
        // Filter out critical permissions for non-super-admin roles
        if (!$role->is_super_admin) {
            $criticalPermissions = PermissionService::CRITICAL_PERMISSIONS;
            $permissions = array_diff($permissions, $criticalPermissions);
        }

        PermissionService::assignPermissionsToRole($role, $permissions);

        // Clear permission cache for all users with this role
        $role->users()->each(function ($user) {
            $user->clearPermissionCache();
        });

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permissions updated for role: {$role->name}");
    }

    public function editUser(User $user)
    {
        $this->authorize('system.manage_permissions');

        // Cannot edit super admin user permissions
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot modify Super Admin user permissions.');
        }

        $user->load(['role.permissions', 'directPermissions']);
        $permissionGroups = PermissionService::getAllPermissionsByGroup();
        $userPermissions = $user->directPermissions->pluck('slug')->toArray();
        $rolePermissions = $user->role ? $user->role->permissions->pluck('slug')->toArray() : [];

        return view('admin.permissions.edit_user', compact('user', 'permissionGroups', 'userPermissions', 'rolePermissions'));
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorize('system.manage_permissions');

        // Cannot edit super admin user permissions
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Cannot modify Super Admin user permissions.');
        }

        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,slug',
        ]);

        $permissions = $request->input('permissions', []);
        
        // Filter out critical permissions
        $criticalPermissions = PermissionService::CRITICAL_PERMISSIONS;
        $permissions = array_diff($permissions, $criticalPermissions);

        // Sync direct permissions
        $permissionIds = Permission::whereIn('slug', $permissions)->pluck('id');
        $user->directPermissions()->sync($permissionIds);
        
        // Clear user's permission cache
        $user->clearPermissionCache();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Direct permissions updated for user: {$user->name}");
    }

    public function clearCache()
    {
        $this->authorize('system.manage_permissions');

        // Clear all permission caches for all users (targeted, not full flush)
        $users = User::all();
        foreach ($users as $user) {
            $user->clearPermissionCache();
            Cache::forget("user:{$user->id}:all_permissions");
        }

        return back()->with('success', 'Permission cache cleared successfully.');
    }

    public function userPermissions(User $user)
    {
        $this->authorize('system.manage_permissions');

        $allPermissions = $user->getAllPermissions();
        $role = $user->role;

        return view('admin.permissions.user_permissions', compact('user', 'allPermissions', 'role'));
    }
}
