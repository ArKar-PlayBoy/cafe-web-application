<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $socialAccounts = $user->socialAccounts;

        return view('user.connected-accounts', [
            'socialAccounts' => $socialAccounts,
            'hasCustomPassword' => $user->hasCustomPassword(),
        ]);
    }

    public function unlink(Request $request, string $provider): RedirectResponse
    {
        $user = $request->user();

        // Validate provider
        if (! in_array($provider, ['google'], true)) {
            abort(404);
        }

        $socialAccount = SocialAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->firstOrFail();

        // Check if unlinking would cause lockout
        $hasPassword = $user->hasCustomPassword();
        $totalSocialAccounts = $user->socialAccounts()->count();

        // Prevent lockout: if only one login method and no password set
        if ($totalSocialAccounts <= 1 && !$hasPassword) {
            return back()->withErrors([
                'unlink' => 'You must set a password before unlinking your only login method. Go to Profile → Set Password first.',
            ]);
        }

        // Audit log before deletion
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'oauth_account_unlinked',
            'resource_type' => 'SocialAccount',
            'resource_id' => $socialAccount->id,
            'old_values' => [
                'provider' => $socialAccount->provider,
                'provider_email' => $socialAccount->provider_email,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'is_critical' => true,
        ]);

        $socialAccount->delete();

        return back()->with('success', ucfirst($provider) . ' account unlinked successfully.');
    }
}
