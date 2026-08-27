<?php

namespace App\Support;

/**
 * Schedule pivot `duration` is stored as integer SECONDS
 * (matches media_schedule_media.duration column and the admin UI).
 */
class MediaScheduleDuration
{
    public const MIN_SECONDS = 5;

    public const DEFAULT_SECONDS = 10;

    /** Upper bound: 8 hours */
    public const MAX_SECONDS = 28800;

    /**
     * Clamp an admin-entered seconds value for storage.
     */
    public static function secondsForStorage(float|int|string|null $seconds, int $fallback = self::DEFAULT_SECONDS): int
    {
        $value = is_numeric($seconds) ? (float) $seconds : $fallback;

        return (int) max(self::MIN_SECONDS, min(self::MAX_SECONDS, round($value)));
    }

    /**
     * Read stored pivot duration as playback seconds.
     *
     * Legacy rows may still contain minutes (0.5, 1, 2…) from the old admin UI.
     * Values below MIN_SECONDS are treated as minutes and converted.
     * (MIN_SECONDS is 5, so a stored 5 is five seconds — not five minutes.)
     */
    public static function secondsFromStored(float|int|string|null $stored, int $fallback = self::DEFAULT_SECONDS): int
    {
        if (!is_numeric($stored)) {
            return $fallback;
        }

        $value = (float) $stored;
        if ($value <= 0) {
            return $fallback;
        }

        // Legacy minutes (e.g. 0.5 → 30s, 1 → 60s). New storage is always >= MIN_SECONDS.
        if ($value < self::MIN_SECONDS) {
            return (int) max(self::MIN_SECONDS, round($value * 60));
        }

        return (int) max(self::MIN_SECONDS, round($value));
    }

    /** @deprecated use secondsForStorage */
    public static function minutesFromSeconds(float|int|string|null $seconds, float $fallbackSeconds = self::DEFAULT_SECONDS): float
    {
        return self::secondsForStorage($seconds, (int) $fallbackSeconds) / 60;
    }

    /** @deprecated use secondsFromStored */
    public static function secondsFromMinutes(float|int|string|null $minutes, int $fallbackSeconds = self::DEFAULT_SECONDS): int
    {
        return self::secondsFromStored($minutes, $fallbackSeconds);
    }
}
