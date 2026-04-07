<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\BannedEmail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $normalizedEmail = BannedEmail::normalizeEmail((string) $request->input('email'));
        $isDenylisted = BannedEmail::isBlocked($normalizedEmail);
        $isBannedUser = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])
            ->where('is_banned', true)
            ->exists();

        if ($isDenylisted || $isBannedUser) {
            return back()->with('status', __('We have emailed your password reset link.'));
        }

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        Password::sendResetLink(['email' => $normalizedEmail]);

        return back()->with('status', __('We have emailed your password reset link.'));
    }
}
