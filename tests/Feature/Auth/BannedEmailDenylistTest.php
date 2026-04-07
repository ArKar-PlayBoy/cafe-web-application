<?php

use App\Models\BannedEmail;
use App\Models\User;

test('ban adds email to denylist and unban removes it', function () {
    $user = User::factory()->create([
        'email' => 'denylist-lifecycle@example.com',
        'is_banned' => false,
    ]);

    $user->ban('Security incident');

    $this->assertDatabaseHas('banned_emails', [
        'email_normalized' => 'denylist-lifecycle@example.com',
        'reason' => 'Security incident',
    ]);

    $user->unban();

    $this->assertDatabaseMissing('banned_emails', [
        'email_normalized' => 'denylist-lifecycle@example.com',
    ]);
});

test('denylist remains even if banned user row is deleted', function () {
    $user = User::factory()->create([
        'email' => 'deleted-banned@example.com',
        'is_banned' => false,
    ]);

    $user->ban('Permanent suspension');
    $user->delete();

    $this->assertDatabaseHas('banned_emails', [
        'email_normalized' => 'deleted-banned@example.com',
    ]);

    expect(BannedEmail::isBlocked('Deleted-Banned@example.com'))->toBeTrue();
});
