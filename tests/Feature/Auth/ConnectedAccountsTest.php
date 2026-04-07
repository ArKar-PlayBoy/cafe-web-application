<?php

use App\Models\SocialAccount;
use App\Models\User;

test('user can view connected accounts page', function () {
    $user = User::factory()->create();

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $response = $this->actingAs($user)->get(route('user.social.accounts'));

    $response->assertStatus(200);
    $response->assertSee('google');
    $response->assertSee($user->email);
});

test('unlink blocked when no password set and only login method', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('user.social.unlink', ['provider' => 'google']));

    $response->assertSessionHasErrors('unlink');
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider' => 'google',
    ]);
});

test('unlink succeeds when password is set', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('user.social.unlink', ['provider' => 'google']));

    $response->assertSessionHas('success');
    $this->assertDatabaseMissing('social_accounts', [
        'user_id' => $user->id,
        'provider' => 'google',
    ]);
});

test('unlink succeeds with multiple social accounts even without password', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-456',
        'provider_email' => 'another@email.com',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('user.social.unlink', ['provider' => 'google']));

    $response->assertSessionHas('success');
    
    // Verify one account was deleted, one remains
    $this->assertDatabaseMissing('social_accounts', [
        'user_id' => $user->id,
        'provider_user_id' => 'google-123',
    ]);
    $this->assertDatabaseHas('social_accounts', [
        'user_id' => $user->id,
        'provider_user_id' => 'google-456',
    ]);
});

test('unlink creates audit log', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    $socialAccount = SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $this->actingAs($user)
        ->delete(route('user.social.unlink', ['provider' => 'google']));

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'oauth_account_unlinked',
        'resource_type' => 'SocialAccount',
    ]);
});

test('cannot unlink other user social account', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    SocialAccount::create([
        'user_id' => $otherUser->id,
        'provider' => 'google',
        'provider_user_id' => 'google-other',
        'provider_email' => $otherUser->email,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('user.social.unlink', ['provider' => 'google']));

    $response->assertStatus(404);
});

test('connected accounts page shows set password prompt when no password', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $response = $this->actingAs($user)->get(route('user.social.accounts'));

    $response->assertStatus(200);
    $response->assertSee('Set a Password');
});

test('connected accounts page hides set password prompt when password exists', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    SocialAccount::create([
        'user_id' => $user->id,
        'provider' => 'google',
        'provider_user_id' => 'google-123',
        'provider_email' => $user->email,
    ]);

    $response = $this->actingAs($user)->get(route('user.social.accounts'));

    $response->assertStatus(200);
    $response->assertDontSee('Set a Password');
});
