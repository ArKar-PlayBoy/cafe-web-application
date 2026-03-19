<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case STRIPE = 'stripe';
    case COD = 'cod';
    case KBZ_PAY = 'kbz_pay';
    case SAVED_CARD = 'saved_';

    public function label(): string
    {
        return match($this) {
            self::STRIPE => 'Credit/Debit Card',
            self::COD => 'Cash on Delivery (COD)',
            self::KBZ_PAY => 'KBZ Pay',
            self::SAVED_CARD => 'Saved Card',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::STRIPE => 'credit-card',
            self::COD => 'cash',
            self::KBZ_PAY => 'mobile',
            self::SAVED_CARD => 'saved',
        };
    }

    public function isOnlinePayment(): bool
    {
        return match($this) {
            self::STRIPE => true,
            default => false,
        };
    }

    public function requiresVerification(): bool
    {
        return match($this) {
            self::KBZ_PAY => true,
            default => false,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values()) || str_starts_with($value, self::SAVED_CARD->value);
    }

    public static function fromValue(string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        
        if (str_starts_with($value, self::SAVED_CARD->value)) {
            return self::SAVED_CARD;
        }
        
        return null;
    }
}
