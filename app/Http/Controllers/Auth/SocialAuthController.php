<?php

namespace App\Http\Controllers\Auth;

use App\Exceptions\OAuthBannedUserException;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BannedEmail;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class SocialAuthController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google'];

    public function redirect(string $provider): RedirectResponse
    {
        if (! $this->isProviderAllowed($provider)) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Invalid authentication provider.']);
        }

        if (! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'This authentication method is currently unavailable.']);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (! $this->isProviderAllowed($provider)) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Invalid authentication provider.']);
        }

        if (! $this->isProviderEnabled($provider)) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'This authentication method is currently unavailable.']);
        }

        try {
            $socialiteUser = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Authentication failed. Please try again.']);
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Authentication failed. Please try again.']);
        }

        $providerEmail = $socialiteUser->getEmail();

        if (! $providerEmail) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Unable to retrieve email from your ' . ucfirst($provider) . ' account.']);
        }

        $providerEmail = BannedEmail::normalizeEmail($providerEmail);

        $denylistedEmail = BannedEmail::findByEmail($providerEmail);
        if ($denylistedEmail) {
            if ($denylistedEmail->reason) {
                session()->flash('ban_reason', $denylistedEmail->reason);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been banned. Contact support.']);
        }

        $providerId = $socialiteUser->getId();
        $providerName = $socialiteUser->getName() ?? Str::before($providerEmail, '@');

        try {
            $user = $this->resolveOrCreateUser($provider, $providerId, $providerEmail, $providerName);
        } catch (OAuthBannedUserException $e) {
            if ($e->banReason) {
                session()->flash('ban_reason', $e->banReason);
            }

            return redirect()->route('login')
                ->withErrors(['email' => 'Your account has been banned. Contact support.']);
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                try {
                    $user = $this->resolveOrCreateUser($provider, $providerId, $providerEmail, $providerName);
                } catch (OAuthBannedUserException $e) {
                    if ($e->banReason) {
                        session()->flash('ban_reason', $e->banReason);
                    }

                    return redirect()->route('login')
                        ->withErrors(['email' => 'Your account has been banned. Contact support.']);
                } catch (Throwable $e) {
                    return redirect()->route('login')
                        ->withErrors(['provider' => 'Authentication failed. Please try again.']);
                }

                Auth::login($user);

                return redirect()->intended(route('dashboard', absolute: false));
            }

            return redirect()->route('login')
                ->withErrors(['provider' => 'Authentication failed. Please try again.']);
        } catch (Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['provider' => 'Authentication failed. Please try again.']);
        }

        Auth::login($user);

        // Audit log for OAuth login
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'oauth_login',
            'resource_type' => 'User',
            'resource_id' => $user->id,
            'new_values' => ['provider' => $provider],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->url(),
            'method' => request()->method(),
            'is_critical' => false,
        ]);

        return redirect()->intended(route('dashboard', absolute: false));
    }

    private function resolveOrCreateUser(string $provider, string $providerId, string $providerEmail, string $providerName): User
    {
        return DB::transaction(function () use ($provider, $providerId, $providerEmail, $providerName) {
            $socialAccount = SocialAccount::where('provider', $provider)
                ->where('provider_user_id', $providerId)
                ->first();

            if ($socialAccount) {
                $user = $socialAccount->user;

                if ($user->isBanned()) {
                    throw new OAuthBannedUserException($user->ban_reason ?? '');
                }

                if ($providerEmail) {
                    $socialAccount->update(['provider_email' => $providerEmail]);
                }

                return $user;
            }

            $user = User::where('email', $providerEmail)->first();

            if ($user) {
                if ($user->isBanned()) {
                    throw new OAuthBannedUserException($user->ban_reason ?? '');
                }

                if (! $user->socialAccounts()
                    ->where('provider', $provider)
                    ->where('provider_user_id', $providerId)
                    ->exists()
                ) {
                    $user->socialAccounts()->create([
                        'provider' => $provider,
                        'provider_user_id' => $providerId,
                        'provider_email' => $providerEmail,
                    ]);

                    // Audit log for linking additional social account
                    AuditLog::create([
                        'user_id' => $user->id,
                        'action' => 'oauth_account_linked',
                        'resource_type' => 'User',
                        'resource_id' => $user->id,
                        'new_values' => [
                            'provider' => $provider,
                            'provider_email' => $providerEmail,
                        ],
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'is_critical' => true,
                    ]);
                }

                if (! $user->email_verified_at) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                return $user;
            }

            // Create new user with OAuth
            $customerRole = Role::lookupCustomerRole();

            $user = User::create([
                'name' => $providerName,
                'email' => $providerEmail,
                'password' => Str::password(64),
                'password_set_at' => null,
            ]);

            if ($customerRole) {
                $user->forceFill(['role_id' => $customerRole->id])->save();
            }

            $user->forceFill(['email_verified_at' => now()])->save();

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_user_id' => $providerId,
                'provider_email' => $providerEmail,
            ]);

            // Dispatch Registered event for new users (triggers welcome email, etc.)
            event(new Registered($user));

            // Audit log for new account linking
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'oauth_account_linked',
                'resource_type' => 'User',
                'resource_id' => $user->id,
                'new_values' => [
                    'provider' => $provider,
                    'provider_email' => $providerEmail,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'is_critical' => true,
            ]);

            return $user;
        });
    }

    private function isProviderAllowed(string $provider): bool
    {
        return in_array($provider, self::ALLOWED_PROVIDERS, true);
    }

    private function isProviderEnabled(string $provider): bool
    {
        return config("social-auth.providers.{$provider}.enabled", false);
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        $code = $e->getCode();

        return in_array($code, [23000, 1062, 23505], true)
            || str_contains($e->getMessage(), 'Unique constraint')
            || str_contains($e->getMessage(), 'Duplicate entry')
            || str_contains($e->getMessage(), 'UNIQUE constraint');
    }
}
