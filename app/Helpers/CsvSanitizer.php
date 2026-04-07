<?php

namespace App\Helpers;

class CsvSanitizer
{
    private const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r", "\n"];

    public static function sanitizeCell(string $value): string
    {
        $trimmed = ltrim($value);

        if ($trimmed === '') {
            return $value;
        }

        foreach (self::DANGEROUS_PREFIXES as $prefix) {
            if (str_starts_with($trimmed, $prefix)) {
                return "'" . $value;
            }
        }

        return $value;
    }
}
