<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BannedEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $normalizedEmail = BannedEmail::normalizeEmail((string) $request->input('email'));
        $request->merge(['email' => $normalizedEmail]);
        $credentials['email'] = $normalizedEmail;

        $throttleKey = Str::lower($request->input('email')).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $user = \App\Models\User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (! $user || ! $user->isAdmin()) {
            RateLimiter::hit($throttleKey);
            Hash::make($request->password);

            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        if ($user->is_banned) {
            if ($user->ban_reason) {
                $request->session()->flash('ban_reason', $user->ban_reason);
            }

            RateLimiter::hit($throttleKey);

            return back()->withErrors([
                'email' => 'Your account has been banned. Contact support.',
            ]);
        }

        if (Auth::guard('admin')->attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
