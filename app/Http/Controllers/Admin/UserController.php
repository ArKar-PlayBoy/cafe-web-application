<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('users.view');

        $users = User::with('role')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('users.manage_staff');

        $currentUser = auth('admin')->user();
        
        // Super admin can assign any role
        // Regular admin can only assign staff, manager, or customer roles
        if ($currentUser->isSuperAdmin()) {
            $roles = Role::all();
        } else {
            $roles = Role::whereNotIn('slug', ['super_admin', 'admin'])->get();
        }

        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('users.manage_staff');

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $currentUser = auth('admin')->user();
        $role = Role::find($request->role_id);

        // Security checks
        if ($role && in_array($role->slug, ['super_admin', 'admin']) && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'You do not have permission to create admin users.');
        }

        $user = User::create([
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => strip_tags($request->phone ?? ''),
            'address' => strip_tags($request->address ?? ''),
            'email_verified_at' => now(),
            'role_id' => $request->role_id,
        ]);

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'user.created',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role ? $role->name : 'None',
            ],
            'is_critical' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorize('users.manage_staff');

        $currentUser = auth('admin')->user();
        
        // Check if user can edit this specific user
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'You cannot edit a Super Admin user.');
        }

        if ($user->isAdmin() && !$currentUser->isSuperAdmin() && $currentUser->id !== $user->id) {
            return back()->with('error', 'Only Super Admins can edit other admin users.');
        }

        // Super admin can assign any role
        // Regular admin can only assign staff, manager, or customer roles
        if ($currentUser->isSuperAdmin()) {
            $roles = Role::all();
        } else {
            $roles = Role::whereNotIn('slug', ['super_admin', 'admin'])->get();
        }

        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('users.manage_staff');

        $currentUser = auth('admin')->user();

        // Security checks
        if ($user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'You cannot edit a Super Admin user.');
        }

        if ($user->isAdmin() && !$currentUser->isSuperAdmin() && $currentUser->id !== $user->id) {
            return back()->with('error', 'Only Super Admins can edit other admin users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'nullable|exists:roles,id',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'password' => 'nullable|string|min:8',
        ]);

        $role = Role::find($request->role_id);

        // Prevent non-super admins from assigning super_admin or admin roles
        if ($role && in_array($role->slug, ['super_admin', 'admin']) && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'You do not have permission to assign admin roles.');
        }

        // Prevent self-demotion from admin
        if ($currentUser->id === $user->id && $currentUser->isAdmin() && $role && !$role->isSuperAdmin) {
            return back()->with('error', 'You cannot demote yourself from admin.');
        }

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ? $user->role->name : 'None',
        ];

        $data = [
            'name' => strip_tags($request->name),
            'email' => $request->email,
            'phone' => strip_tags($request->phone ?? ''),
            'address' => strip_tags($request->address ?? ''),
        ];
        
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->role_id = $request->role_id;
        $user->save();

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'user.updated',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'old_values' => $oldValues,
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $role ? $role->name : 'None',
            ],
            'is_critical' => false,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('users.manage_staff');

        $currentUser = auth('admin')->user();

        // Use Gate for complex permission checking
        $canDelete = Gate::allows('delete-user', $user);

        if (!$canDelete) {
            $result = PermissionService::canDeleteUser($currentUser, $user);
            return back()->with('error', $result['reason']);
        }

        // Soft delete instead of hard delete
        $user->delete();

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'user.deleted',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'old_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ? $user->role->name : 'None',
            ],
            'is_critical' => true,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function ban(Request $request, User $user)
    {
        $this->authorize('users.ban');

        $currentUser = auth('admin')->user();

        // Use Gate for complex permission checking
        $canBan = Gate::allows('ban-user', $user);

        if (!$canBan) {
            $result = PermissionService::canBanUser($currentUser, $user);
            return back()->with('error', $result['reason']);
        }

        $request->validate([
            'ban_reason' => 'nullable|string|max:255',
        ]);

        $user->ban($request->ban_reason);

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'user.banned',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'new_values' => [
                'banned' => true,
                'reason' => $request->ban_reason,
            ],
            'is_critical' => true,
        ]);

        return back()->with('success', 'User has been banned successfully.');
    }

    public function unban(User $user)
    {
        $this->authorize('users.ban');

        $currentUser = auth('admin')->user();

        // Cannot unban super admins
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'Super Admins cannot be unbanned.');
        }

        // Only super admins can unban other admins
        if ($user->isAdmin() && !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admins can unban admin users.');
        }

        $user->unban();

        // Log the action
        AuditLog::create([
            'user_id' => $currentUser->id,
            'action' => 'user.unbanned',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'new_values' => ['banned' => false],
            'is_critical' => true,
        ]);

        return back()->with('success', 'User has been unbanned successfully.');
    }
}
