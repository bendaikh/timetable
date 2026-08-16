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

    /**
     * Normalize announcement title/body font sizes.
     * Legacy rows stored px integers (12–160); new rows store rem (≈0.75–10).
     */
    public static function normalizeAnnouncementRem(mixed $value, string $default = '1.5rem'): string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $trimmed = trim((string) $value);
        if ($trimmed === '') {
            return $default;
        }

        if (str_ends_with(strtolower($trimmed), 'rem')) {
            return self::normalizeBoxRem($trimmed, $default);
        }

        if (str_ends_with(strtolower($trimmed), 'px')) {
            return self::normalizeRem($trimmed, $default);
        }

        if (is_numeric($trimmed)) {
            $numeric = (float) $trimmed;
            // Historical announcement px integers were typically > 10.
            if ($numeric > 10) {
                return self::normalizeRem($trimmed, $default);
            }

            return self::normalizeBoxRem($trimmed, $default);
        }

        return $default;
    }

    private static function formatRem(float $rem): string
    {
        $formatted = rtrim(rtrim(number_format($rem, 3, '.', ''), '0'), '.');

        return $formatted . 'rem';
    }

    /**
     * Ensure prayer table column widths fill the panel (no leftover empty track).
     * Keeps the prayer-name share when possible; leftover width is split across time columns.
     *
     * @param  array<int, mixed>|null  $widths
     * @return array{0: string, 1: string, 2: string}
     */
    public static function normalizePrayerColumnWidths(?array $widths, array $defaults = ['30%', '35%', '35%']): array
    {
        $defaults = array_values($defaults);
        while (count($defaults) < 3) {
            $defaults[] = '33.333%';
        }

        if (!is_array($widths) || count($widths) < 3) {
            return [self::formatPercent($defaults[0]), self::formatPercent($defaults[1]), self::formatPercent($defaults[2])];
        }

        $parsed = [];
        for ($i = 0; $i < 3; $i++) {
            $parsed[] = self::parsePercent($widths[$i] ?? null);
        }

        if (in_array(null, $parsed, true)) {
            return [self::formatPercent($defaults[0]), self::formatPercent($defaults[1]), self::formatPercent($defaults[2])];
        }

        $sum = $parsed[0] + $parsed[1] + $parsed[2];

        // Already fills the row closely enough.
        if ($sum >= 95 && $sum <= 105) {
            if (abs($sum - 100) > 0.05) {
                $scale = 100 / $sum;
                $parsed = array_map(static fn ($value) => $value * $scale, $parsed);
            }

            return [
                self::formatPercent($parsed[0]),
                self::formatPercent($parsed[1]),
                self::formatPercent($parsed[2]),
            ];
        }

        // Underfilled rows leave a visible gap before announcements: keep name width, fill the rest.
        if ($sum < 95) {
            $name = min(max($parsed[0], 20), 40);
            $remaining = max(100 - $name, 60);
            $timeShare = $remaining / 2;

            return [
                self::formatPercent($name),
                self::formatPercent($timeShare),
                self::formatPercent($timeShare),
            ];
        }

        // Overfilled: scale down proportionally.
        $scale = 100 / $sum;

        return [
            self::formatPercent($parsed[0] * $scale),
            self::formatPercent($parsed[1] * $scale),
            self::formatPercent($parsed[2] * $scale),
        ];
    }

    private static function parsePercent(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $trimmed = trim((string) $value);
        if (preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*%?$/', $trimmed, $matches)) {
            return (float) $matches[1];
        }

        return null;
    }

    private static function formatPercent(float|string $value): string
    {
        $numeric = is_numeric($value) ? (float) $value : (self::parsePercent($value) ?? 0.0);
        $formatted = rtrim(rtrim(number_format($numeric, 3, '.', ''), '0'), '.');

        return $formatted . '%';
    }
}
