<?php

namespace App\Support;

use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use Carbon\Carbon;
use DateTimeInterface;

/**
 * Single source of truth for resolving iqamah (jamaat) clock times used by
 * posters, countdowns, and admin previews.
 */
class PrayerJamaatTime
{
    public static function appTimezone(): string
    {
        return (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
    }

    public static function now(?Carbon $now = null): Carbon
    {
        return $now
            ? $now->copy()->timezone(self::appTimezone())
            : Carbon::now(self::appTimezone());
    }

    /**
     * Resolve iqamah time for a prayer on the timetable row's date.
     */
    public static function resolve(PrayerTime $prayerTimes, string $prayerName, ?Carbon $referenceNow = null): ?Carbon
    {
        $referenceNow = self::now($referenceNow);
        $date = self::resolvePrayerDate($prayerTimes, $referenceNow);

        $jamaatField = $prayerName . '_jamaat';
        $explicitJamaat = self::normalizeClockValue($prayerTimes->$jamaatField ?? null);
        if ($explicitJamaat !== null) {
            return self::parseClockOnDate($date, $explicitJamaat);
        }

        $beginning = self::normalizeClockValue($prayerTimes->$prayerName ?? null);
        if ($beginning === null) {
            return null;
        }

        $offsetMinutes = (int) Setting::get($prayerName . '_jamaat_offset', 0);

        return self::parseClockOnDate($date, $beginning)?->addMinutes($offsetMinutes);
    }

    /**
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon}|null
     */
    public static function beforePrayerPosterWindow(
        MediaSchedule $schedule,
        PrayerTime $prayerTimes,
        ?Carbon $referenceNow = null
    ): ?array {
        if ($schedule->schedule_type !== 'minutes_before_prayer'
            || !$schedule->prayer_name
            || !$schedule->minutes_before_prayer) {
            return null;
        }

        $jamaat = self::resolve($prayerTimes, $schedule->prayer_name, $referenceNow);
        if (!$jamaat) {
            return null;
        }

        return [
            'jamaat' => $jamaat->copy(),
            'start' => $jamaat->copy()->subMinutes((int) $schedule->minutes_before_prayer),
            'end' => $jamaat->copy()->subMinutes(5),
        ];
    }

    /**
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon}|null
     */
    public static function afterPrayerPosterWindow(
        MediaSchedule $schedule,
        PrayerTime $prayerTimes,
        ?Carbon $referenceNow = null
    ): ?array {
        if ($schedule->schedule_type !== 'minutes_after_prayer'
            || !$schedule->prayer_name
            || !$schedule->minutes_after_prayer) {
            return null;
        }

        $jamaat = self::resolve($prayerTimes, $schedule->prayer_name, $referenceNow);
        if (!$jamaat) {
            return null;
        }

        $minutesAfter = (int) $schedule->minutes_after_prayer;

        return [
            'jamaat' => $jamaat->copy(),
            'start' => $jamaat->copy()->addMinutes($minutesAfter),
            'end' => $jamaat->copy()->addMinutes($minutesAfter + 10),
        ];
    }

    public static function isWithinWindow(Carbon $now, Carbon $start, Carbon $end): bool
    {
        return $now->gte($start) && $now->lte($end);
    }

    public static function parseClockOnDate(string $date, string $time): ?Carbon
    {
        $normalized = self::normalizeClockValue($time);
        if ($normalized === null) {
            return null;
        }

        return Carbon::parse($date . ' ' . $normalized, self::appTimezone());
    }

    public static function normalizeClockValue(mixed $value): ?string
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('H:i:s');
        }

        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        if (preg_match('/^\d{1,2}:\d{2}$/', $trimmed) === 1) {
            return $trimmed . ':00';
        }

        if (preg_match('/^\d{1,2}:\d{2}:\d{2}$/', $trimmed) === 1) {
            return $trimmed;
        }

        try {
            return Carbon::parse($trimmed)->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function resolvePrayerDate(PrayerTime $prayerTimes, Carbon $referenceNow): string
    {
        if ($prayerTimes->date) {
            return Carbon::parse($prayerTimes->date)->toDateString();
        }

        return $referenceNow->toDateString();
    }
}
