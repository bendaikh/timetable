<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

/**
 * Keeps PHP/Laravel default timezone aligned with the mosque Setting timezone.
 * Poster/countdown comparisons still pass an explicit TZ; this prevents bare
 * Carbon::parse() / date casts from drifting when APP_TIMEZONE ≠ mosque TZ.
 */
class MosqueTimezone
{
    public static function resolve(?string $fallback = null): string
    {
        $fallback = $fallback
            ?: (string) config('app.timezone', 'Europe/London');

        try {
            $configured = Setting::get('timezone', $fallback);
        } catch (Throwable) {
            $configured = $fallback;
        }

        $timezone = is_string($configured) && $configured !== ''
            ? $configured
            : $fallback;

        return self::isValid($timezone) ? $timezone : 'Europe/London';
    }

    public static function apply(?string $timezone = null): string
    {
        $timezone = self::resolve($timezone);

        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);

        return $timezone;
    }

    public static function isValid(string $timezone): bool
    {
        return in_array($timezone, timezone_identifiers_list(), true);
    }
}
