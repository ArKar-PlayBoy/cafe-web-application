<?php

use App\Models\BannedEmail;
use App\Models\Role;
use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('registered user is assigned customer role', function () {
    $customerRole = Role::updateOrCreate(
        ['slug' => 'customer'],
        [
            'name' => 'Customer',
            'description' => 'Regular customer account',
            'is_super_admin' => false,
        ]
    );

    $response = $this->post('/register', [
        'name' => 'Role Test User',
        'email' => 'role-test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();

    $user = \App\Models\User::where('email', 'role-test@example.com')->first();

    expect($user->role_id)->toBe($customerRole->id);
});

test('denylisted email cannot register', function () {
    BannedEmail::blockEmail('blocked@example.com', 'Policy violation');

    $response = $this->post('/register', [
        'name' => 'Blocked User',
        'email' => 'Blocked@Example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
    $this->assertDatabaseMissing('users', ['email' => 'blocked@example.com']);
});

test('banned existing email cannot register even if denylist missing', function () {
    User::factory()->create([
        'email' => 'banned-existing@example.com',
        'is_banned' => true,
    ]);

    $response = $this->post('/register', [
        'name' => 'Blocked Existing',
        'email' => 'BANNED-EXISTING@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});
