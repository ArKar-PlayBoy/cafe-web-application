<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BannedEmail extends Model
{
    protected $fillable = [
        'email_normalized',
        'reason',
    ];

    public static function normalizeEmail(string $email): string
    {
        return Str::lower(trim($email));
    }

    public static function findByEmail(string $email): ?self
    {
        $normalizedEmail = self::normalizeEmail($email);

        return self::where('email_normalized', $normalizedEmail)->first();
    }

    public static function isBlocked(string $email): bool
    {
        return self::findByEmail($email) !== null;
    }

    public static function blockEmail(string $email, ?string $reason = null): self
    {
        $normalizedEmail = self::normalizeEmail($email);

        return self::updateOrCreate(
            ['email_normalized' => $normalizedEmail],
            ['reason' => $reason]
        );
    }

    public static function unblockEmail(string $email): void
    {
        $normalizedEmail = self::normalizeEmail($email);

        self::where('email_normalized', $normalizedEmail)->delete();
    }
}
