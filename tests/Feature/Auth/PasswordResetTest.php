<?php

use App\Models\BannedEmail;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
        $response = $this->get('/reset-password/'.$notification->token);

        $response->assertStatus(200);

        return true;
    });
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));

        return true;
    });
});

test('denylisted email reset request is silently blocked', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'denylisted@example.com']);
    BannedEmail::blockEmail('denylisted@example.com', 'Fraud detected');

    $response = $this->post('/forgot-password', ['email' => 'DenyListed@Example.com']);

    $response->assertSessionHas('status', 'We have emailed your password reset link.');
    Notification::assertNothingSent();
});

test('banned user reset request is silently blocked', function () {
    Notification::fake();

    $user = User::factory()->create([
        'email' => 'banned-reset@example.com',
        'is_banned' => true,
    ]);

    $response = $this->post('/forgot-password', ['email' => 'BANNED-RESET@example.com']);

    $response->assertSessionHas('status', 'We have emailed your password reset link.');
    Notification::assertNothingSent();
});

test('banned user cannot reset password with previously issued token', function () {
    Notification::fake();

    $user = User::factory()->create(['email' => 'token-user@example.com']);

    $this->post('/forgot-password', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
        $user->ban('Policy violation');

        $response = $this->post('/reset-password', [
            'token' => $notification->token,
            'email' => 'Token-User@Example.com',
            'password' => 'new-secure-password',
            'password_confirmation' => 'new-secure-password',
        ]);

        $response->assertSessionHasErrors('email');

        expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeFalse();

        return true;
    });
});
