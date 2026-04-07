<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BannedEmail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $normalizedEmail = BannedEmail::normalizeEmail((string) $request->input('email'));
        $request->merge(['email' => $normalizedEmail]);

        $isDenylisted = BannedEmail::isBlocked($normalizedEmail);
        $hasBannedUser = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->where('is_banned', true)
            ->exists();

        if ($isDenylisted || $hasBannedUser) {
            return back()->withErrors([
                'email' => 'This email address cannot be used to register.',
            ])->withInput($request->only('name', 'email'));
        }

        $customerRole = Role::lookupCustomerRole();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'password_set_at' => now(),
        ]);

        if ($customerRole) {
            $user->forceFill(['role_id' => $customerRole->id])->save();
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
