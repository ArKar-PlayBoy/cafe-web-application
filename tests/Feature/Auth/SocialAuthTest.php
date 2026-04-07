<?php

use App\Models\BannedEmail;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Clear role cache first to ensure fresh data
    Role::clearCache('customer');
    
    Role::updateOrCreate(
        ['slug' => 'customer'],
        [
            'name' => 'Customer',
            'description' => 'Regular customer account',
            'is_super_admin' => false,
        ]
    );
    
    // Clear cache again after role creation
    Role::clearCache('customer');
});

function mockSocialiteUser(array $attributes = []): SocialiteUser
{
    $user = new SocialiteUser();
    $user->map([
        'id' => $attributes['id'] ?? 'google-123',
        'email' => $attributes['email'] ?? 'google@example.com',
        'name' => $attributes['name'] ?? 'Google User',
    ]);

    return $user;
}

function mockGoogleCallback(array $attributes = []): void
{
    $socialiteUser = mockSocialiteUser($attributes);

    $mockProvider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $mockProvider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn($mockProvider);
}

test('login page shows google button when enabled', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $response = $this->get(route('login'));

    $response->assertSee('Continue with Google');
    $response->assertSee(route('auth.provider.redirect', ['provider' => 'google']));
});

test('login page hides google button when disabled', function () {
    Config::set('social-auth.providers.google.enabled', false);

    $response = $this->get(route('login'));

    $response->assertDontSee('Continue with Google');
});

test('register page shows google button when enabled', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $response = $this->get(route('register'));

    $response->assertSee('Continue with Google');
});

test('register page hides google button when disabled', function () {
    Config::set('social-auth.providers.google.enabled', false);

    $response = $this->get(route('register'));

    $response->assertDontSee('Continue with Google');
});

test('redirect rejects unknown providers', function () {
    $response = $this->get('/auth/facebook/redirect');

    $response->assertStatus(404);
});

test('redirect rejects disabled providers', function () {
    Config::set('social-auth.providers.google.enabled', false);

    $response = $this->get(route('auth.provider.redirect', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['provider' => 'This authentication method is currently unavailable.']);
});

test('callback rejects unknown providers', function () {
    $response = $this->get('/auth/facebook/callback');

    $response->assertStatus(404);
});

test('callback rejects disabled providers', function () {
    Config::set('social-auth.providers.google.enabled', false);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['provider' => 'This authentication method is currently unavailable.']);
});

test('callback creates new verified customer user and social account', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();
    expect($customerRole)->not->toBeNull('Customer role should exist');

    mockGoogleCallback();

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $user = User::where('email', 'google@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull('User email_verified_at should be set')
        ->and($user->role_id)->toBe($customerRole->id)
        ->and($user->socialAccounts)->toHaveCount(1)
        ->and($user->socialAccounts->first()->provider)->toBe('google')
        ->and($user->socialAccounts->first()->provider_user_id)->toBe('google-123')
        ->and($user->socialAccounts->first()->provider_email)->toBe('google@example.com');

    $response->assertRedirect(route('dashboard'));
});

test('email auto-link reuses existing user and creates new social account', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();

    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'role_id' => $customerRole->id,
        'email_verified_at' => null,
    ]);

    mockGoogleCallback(['email' => 'existing@example.com']);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $existingUser->refresh();

    expect($existingUser->socialAccounts)->toHaveCount(1)
        ->and($existingUser->socialAccounts->first()->provider)->toBe('google')
        ->and($existingUser->socialAccounts->first()->provider_user_id)->toBe('google-123')
        ->and($existingUser->email_verified_at)->not->toBeNull();

    $response->assertRedirect(route('dashboard'));
});

test('existing provider link logs in correct user', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();

    $user = User::factory()->create([
        'email' => 'linked@example.com',
        'role_id' => $customerRole->id,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => 'linked@example.com',
    ]);

    mockGoogleCallback([
        'id' => 'google-123',
        'email' => 'linked@example.com',
    ]);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('repeated callback with same provider identity is idempotent', function () {
    Config::set('social-auth.providers.google.enabled', true);

    mockGoogleCallback();
    mockGoogleCallback();

    $this->get(route('auth.provider.callback', ['provider' => 'google']));
    $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $user = User::where('email', 'google@example.com')->first();

    expect($user->socialAccounts()->where('provider', 'google')->count())->toBe(1);
});

test('banned linked user cannot authenticate via oauth', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();

    $user = User::factory()->create([
        'email' => 'banned@example.com',
        'role_id' => $customerRole->id,
        'is_banned' => true,
        'ban_reason' => 'Violation of terms',
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-banned',
        'provider_email' => 'banned@example.com',
    ]);

    mockGoogleCallback([
        'id' => 'google-banned',
        'email' => 'banned@example.com',
    ]);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
});

test('denylisted email cannot authenticate via oauth even without user record', function () {
    Config::set('social-auth.providers.google.enabled', true);
    BannedEmail::blockEmail('denylisted@example.com', 'Abuse prevention');

    mockGoogleCallback([
        'id' => 'google-denylisted',
        'email' => 'Denylisted@example.com',
    ]);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['email']);
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'denylisted@example.com']);
});

test('missing provider email fails gracefully', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $socialiteUser = new SocialiteUser();
    $socialiteUser->id = 'google-no-email';
    $socialiteUser->name = 'No Email User';
    $socialiteUser->email = null;
    $socialiteUser->avatar = null;

    $mockProvider = Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
    $mockProvider->shouldReceive('user')->andReturn($socialiteUser);

    Socialite::shouldReceive('driver')
        ->with('google')
        ->andReturn($mockProvider);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors(['provider']);
    $this->assertGuest();
});

test('existing password auth tests remain green', function () {
    Config::set('social-auth.providers.google.enabled', false);

    $customerRole = Role::where('slug', 'customer')->first();

    $user = User::factory()->create([
        'email' => 'password-user@example.com',
        'password' => bcrypt('password'),
        'role_id' => $customerRole->id,
    ]);

    $response = $this->post(route('login'), [
        'email' => 'password-user@example.com',
        'password' => 'password',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);
});

test('oauth user has password_set_at set to null', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();
    expect($customerRole)->not->toBeNull();

    mockGoogleCallback();

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $user = User::where('email', 'google@example.com')->first();

    expect($user)->not->toBeNull()
        ->and($user->password_set_at)->toBeNull('OAuth user should not have password_set_at');
});

test('email-linked oauth user keeps existing password_set_at', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();

    $existingUser = User::factory()->create([
        'email' => 'existing@example.com',
        'role_id' => $customerRole->id,
        'email_verified_at' => null,
        'password_set_at' => now()->subDay(),
    ]);

    $originalPasswordSetAt = $existingUser->password_set_at;

    mockGoogleCallback(['email' => 'existing@example.com']);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $existingUser->refresh();

    expect($existingUser->password_set_at)->not->toBeNull('Email-linked user should keep their existing password_set_at')
        ->and($existingUser->password_set_at->equalTo($originalPasswordSetAt))->toBeTrue('password_set_at value should be preserved');
});

test('hasCustomPassword returns false for oauth user', function () {
    Config::set('social-auth.providers.google.enabled', true);

    mockGoogleCallback();

    $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $user = User::where('email', 'google@example.com')->first();

    expect($user->hasCustomPassword())->toBeFalse('OAuth user should not have custom password');
});

test('hasCustomPassword returns true for normal user with password', function () {
    $customerRole = Role::where('slug', 'customer')->first();

    $user = User::factory()->create([
        'email' => 'normal-user@example.com',
        'role_id' => $customerRole->id,
        'password_set_at' => now(),
    ]);

    expect($user->hasCustomPassword())->toBeTrue('User with password_set_at should have custom password');
});

test('audit log created for oauth login', function () {
    Config::set('social-auth.providers.google.enabled', true);

    $customerRole = Role::where('slug', 'customer')->first();

    $existingUser = User::factory()->create([
        'email' => 'linked@example.com',
        'role_id' => $customerRole->id,
    ]);

    SocialAccount::create([
        'user_id' => $existingUser->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => 'linked@example.com',
    ]);

    mockGoogleCallback([
        'id' => 'google-123',
        'email' => 'linked@example.com',
    ]);

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $existingUser->id,
        'action' => 'oauth_login',
    ]);
});

test('audit log created for new oauth account linking', function () {
    Config::set('social-auth.providers.google.enabled', true);

    mockGoogleCallback();

    $response = $this->get(route('auth.provider.callback', ['provider' => 'google']));

    $user = User::where('email', 'google@example.com')->first();

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'oauth_account_linked',
        'resource_type' => 'User',
    ]);
});
