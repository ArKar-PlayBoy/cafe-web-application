<?php

namespace App\Listeners;

use App\Events\RolePermissionsUpdated;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ClearRoleUserPermissionsCache
{
    public function handle(RolePermissionsUpdated $event): void
    {
        $role = $event->role;

        // Clear cache for all users with this role
        $users = User::where('role_id', $role->id)->get();

        foreach ($users as $user) {
            Cache::forget("user:{$user->id}:permissions");
        }
    }
}
