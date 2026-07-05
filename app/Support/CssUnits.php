<?php

namespace App\Support;

class CssUnits
{
    /**
     * Convert legacy px values to rem (e.g. announcement font_size integers: 24, 36).
     */
    public static function normalizeRem(mixed $value, string $default = '1.2rem'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return $default;
        }

        if (str_ends_with(strtolower($trimmed), 'rem')) {
            return $trimmed;
        }

        if (str_ends_with(strtolower($trimmed), 'px')) {
            $numeric = (float) rtrim($trimmed, 'pxPx');

            return self::formatRem($numeric / 16);
        }

        if (is_numeric($trimmed)) {
            return self::formatRem(((float) $trimmed) / 16);
        }

        return $default;
    }

    /**
     * Normalize admin box / sliding-text font sizes.
     * Bare numbers are rem (legacy Blade: value + 'rem'); px suffix still converts.
     */
    public static function normalizeBoxRem(mixed $value, string $default = '1rem'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $trimmed = trim((string) $value);

        if ($trimmed === '') {
            return $default;
        }

        if (str_ends_with(strtolower($trimmed), 'rem')) {
            return $trimmed;
        }

        if (str_ends_with(strtolower($trimmed), 'px')) {
            $numeric = (float) rtrim($trimmed, 'pxPx');

            return self::formatRem($numeric / 16);
        }

        if (is_numeric($trimmed)) {
            return self::formatRem((float) $trimmed);
        }

        return $default;
    }

    private static function formatRem(float $rem): string
    {
        $formatted = rtrim(rtrim(number_format($rem, 3, '.', ''), '0'), '.');

        return $formatted . 'rem';
    }
}
