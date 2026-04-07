<?php

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('password can be updated', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');
});

test('oauth user can set password without current password', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    $this->assertNotNull($user->refresh()->password_set_at, 'password_set_at should be set after password update');
});

test('normal user requires current password to update', function () {
    $user = User::factory()->create([
        'password_set_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->put('/password', [
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('updatePassword', 'current_password')
        ->assertRedirect('/profile');
});

test('password update sets password_set_at timestamp', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    $this->actingAs($user)->put('/password', [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $user->refresh();

    expect($user->password_set_at)->not->toBeNull();
});

test('password update creates audit log', function () {
    $user = User::factory()->create([
        'password_set_at' => null,
    ]);

    $this->actingAs($user)->put('/password', [
        'password' => 'new-password',
        'password_confirmation' => 'new-password',
    ]);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $user->id,
        'action' => 'password_changed',
        'resource_type' => 'User',
    ]);
});
