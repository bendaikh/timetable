<?php

namespace App\Support;

use App\Models\PrayerTime;
use Carbon\Carbon;

/**
 * Chooses today vs tomorrow timetable row values for on-screen display.
 * After a prayer's jamaat time has passed, that row shows tomorrow's times.
 */
class PrayerTimeDisplayRollover
{
    public const PRAYERS = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];

    public static function nowSeconds(?Carbon $now = null): int
    {
        $now = PrayerJamaatTime::now($now);

        return self::clockToSeconds($now->format('H:i:s')) ?? 0;
    }

    public static function clockToSeconds(?string $time): ?int
    {
        $normalized = PrayerJamaatTime::normalizeClockValue($time);
        if ($normalized === null) {
            return null;
        }

        [$hours, $minutes, $seconds] = array_map('intval', explode(':', $normalized));

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }

    public static function formatClockHms(?string $time): ?string
    {
        $normalized = PrayerJamaatTime::normalizeClockValue($time);
        if ($normalized === null) {
            return null;
        }

        return substr($normalized, 0, 5);
    }

    public static function hasJamaatPassed(?PrayerTime $today, string $prayer, ?Carbon $now = null): bool
    {
        if (!$today) {
            return false;
        }

        $jamaat = PrayerJamaatTime::resolve($today, $prayer, $now);
        if (!$jamaat) {
            return false;
        }

        $jamaatSeconds = self::clockToSeconds($jamaat->format('H:i:s'));
        if ($jamaatSeconds === null) {
            return false;
        }

        return self::nowSeconds($now) >= $jamaatSeconds;
    }

    public static function sourceRecord(
        ?PrayerTime $today,
        ?PrayerTime $tomorrow,
        string $prayer,
        ?Carbon $now = null
    ): ?PrayerTime {
        if (self::hasJamaatPassed($today, $prayer, $now) && $tomorrow) {
            return $tomorrow;
        }

        return $today;
    }

    public static function resolvePrayerRow(
        ?PrayerTime $today,
        ?PrayerTime $tomorrow,
        string $prayer,
        ?Carbon $now = null
    ): array {
        $source = self::sourceRecord($today, $tomorrow, $prayer, $now);
        if (!$source) {
            return [
                'beginning' => null,
                'jamaat' => null,
                'adhan' => null,
            ];
        }

        return [
            'beginning' => self::formatClockHms($source->$prayer ?? null),
            'jamaat' => self::formatClockHms(PrayerJamaatTime::resolve($source, $prayer, $now)?->format('H:i:s')),
            'adhan' => self::formatClockHms(PrayerAdhanTime::resolve($source, $prayer, $now)?->format('H:i:s')),
        ];
    }

    /**
     * @return array<string, array{beginning: ?string, jamaat: ?string, adhan: ?string}>
     */
    public static function resolveAllPrayerRows(
        ?PrayerTime $today,
        ?PrayerTime $tomorrow,
        ?Carbon $now = null
    ): array {
        $rows = [];
        foreach (self::PRAYERS as $prayer) {
            $rows[$prayer] = self::resolvePrayerRow($today, $tomorrow, $prayer, $now);
        }

        return $rows;
    }

    public static function resolveSpecialTimeValue(
        ?PrayerTime $today,
        ?PrayerTime $tomorrow,
        string $field,
        ?Carbon $now = null,
        ?string $rolloverPrayer = null
    ): ?string {
        $todayValue = $today ? self::formatClockHms($today->$field ?? null) : null;

        $shouldRoll = false;
        if ($rolloverPrayer !== null) {
            $shouldRoll = self::hasJamaatPassed($today, $rolloverPrayer, $now);
        } elseif ($todayValue !== null) {
            $seconds = self::clockToSeconds($todayValue);
            $shouldRoll = $seconds !== null && self::nowSeconds($now) >= $seconds;
        }

        if ($shouldRoll && $tomorrow) {
            return self::formatClockHms($tomorrow->$field ?? null);
        }

        return $todayValue;
    }

    /**
     * @return array<string, ?string>
     */
    public static function resolveSpecialTimes(
        ?PrayerTime $today,
        ?PrayerTime $tomorrow,
        ?Carbon $now = null
    ): array {
        return [
            'sehri_ends' => self::resolveSpecialTimeValue($today, $tomorrow, 'fajr', $now, 'fajr'),
            'sun_rise' => self::resolveSpecialTimeValue($today, $tomorrow, 'sun_rise', $now),
            'noon' => self::resolveSpecialTimeValue($today, $tomorrow, 'zohar', $now, 'zohar'),
            'jumah_1' => self::resolveSpecialTimeValue($today, $tomorrow, 'jumah_1', $now),
            'jumah_2' => self::resolveSpecialTimeValue($today, $tomorrow, 'jumah_2', $now),
            'eid_prayer_1' => self::resolveSpecialTimeValue($today, $tomorrow, 'eid_prayer_1', $now),
            'eid_prayer_2' => self::resolveSpecialTimeValue($today, $tomorrow, 'eid_prayer_2', $now),
        ];
    }

    public static function formatDisplayTime(?string $time24): string
    {
        if (!$time24) {
            return '--:--';
        }

        [$hours, $minutes] = array_map('intval', explode(':', $time24));
        $displayHours = $hours % 12 ?: 12;

        return sprintf('%d:%02d', $displayHours, $minutes);
    }
}
