<?php

namespace App\Services;

class PhoneNumber
{
    /**
     * Indian mobile: strip spaces, +91 and a leading 0, then require ^[6-9]\d{9}$.
     */
    public static function normalize(string $raw): ?string
    {
        $digits = preg_replace('/\D+/', '', trim($raw)) ?? '';
        if (str_starts_with($digits, '91') && strlen($digits) === 12) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
            $digits = substr($digits, 1);
        }
        if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
            return null;
        }
        return $digits;
    }
}
