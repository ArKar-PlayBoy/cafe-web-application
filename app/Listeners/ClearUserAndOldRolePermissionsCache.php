<?php

namespace App\Listeners;

use App\Events\UserRoleChanged;
use Illuminate\Support\Facades\Cache;

class ClearUserAndOldRolePermissionsCache
{
    public function handle(UserRoleChanged $event): void
    {
        $user = $event->user;
        $oldRoleId = $event->oldRoleId;

        // Clear cache for the user whose role changed
        Cache::forget("user:{$user->id}:permissions");

        // Also clear cache for users with the old role (in case role permissions changed)
        if ($oldRoleId) {
            $oldRoleUsers = \App\Models\User::where('role_id', $oldRoleId)->get();
            foreach ($oldRoleUsers as $oldRoleUser) {
                Cache::forget("user:{$oldRoleUser->id}:permissions");
            }
        }
    }
}
