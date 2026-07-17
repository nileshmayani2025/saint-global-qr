<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Mobile numbers are the login identity, so every number has to reduce to one
 * canonical form — otherwise "+91 98765 43210" and "9876543210" would register
 * as two separate accounts.
 */
final class Phone
{
    /** Indian mobile numbers: 10 digits, first digit 6-9. */
    public const LENGTH = 10;

    /**
     * Reduce any user-typed number to its bare 10 digits, dropping the country
     * code (+91), a leading trunk 0, and any spacing / dashes / brackets.
     */
    public static function normalize(?string $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value) ?? '';

        return strlen($digits) > self::LENGTH
            ? substr($digits, -self::LENGTH)
            : $digits;
    }

    public static function isValid(?string $value): bool
    {
        return preg_match('/^[6-9]\d{9}$/', self::normalize($value)) === 1;
    }

    /**
     * Group for display: 9876543210 → 98765 43210.
     */
    public static function format(?string $value): string
    {
        $phone = self::normalize($value);

        return strlen($phone) === self::LENGTH
            ? substr($phone, 0, 5).' '.substr($phone, 5)
            : $phone;
    }
}
