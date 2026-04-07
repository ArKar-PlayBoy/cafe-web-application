<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'password' => ['required', Password::defaults(), 'confirmed'],
        ];

        // Only require current password if user has set their own password
        if ($user->hasCustomPassword()) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validateWithBag('updatePassword', $rules);

        $user->update([
            'password' => Hash::make($validated['password']),
            'password_set_at' => now(),
        ]);

        // Audit log for password change
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'password_changed',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'old_values' => ['password_set_at' => $user->getOriginal('password_set_at')],
            'new_values' => ['password_set_at' => now()->toIso8601String()],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_critical' => true,
        ]);

        return back()->with('status', 'password-updated');
    }
}
