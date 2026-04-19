<?php

namespace App\Observers;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    public function updated(User $user): void
    {
        if ($user->wasChanged(['role_id', 'is_banned'])) {
            CacheService::forget(CacheService::userPermissions($user->id));
            CacheService::forget(CacheService::allUserPermissions($user->id));
        }
    }

    public function deleted(User $user): void
    {
        CacheService::forget(CacheService::userPermissions($user->id));
        CacheService::forget(CacheService::allUserPermissions($user->id));
    }
}