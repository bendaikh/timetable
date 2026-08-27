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
    /**
     * Fixed playback length for after-prayer posters (minutes after the start offset).
     * "10 minutes after jamaat" → start at jamaat+10, remain active for this many minutes.
     */
    public const AFTER_POSTER_WINDOW_MINUTES = 10;

    public static function appTimezone(): string
    {
        return MosqueTimezone::resolve((string) config('app.timezone'));
    }

    public static function now(?Carbon $now = null): Carbon
    {
        return $now
            ? $now->copy()->timezone(self::appTimezone())
            : Carbon::now(self::appTimezone());
    }

    /**
     * Resolve the prayer-relative reference clock: JAMAAT (iqamah), never Adhan.
     *
     * Priority:
     * 1) Explicit {prayer}_jamaat column from the uploaded timetable
     * 2) Beginning time ({prayer}) + settings {prayer}_jamaat_offset
     *
     * The {prayer}_adhan column is intentionally ignored for posters/countdowns.
     * When Adhan == Jamaat (e.g. Maghrib), the jamaat column (or beginning+0) is used.
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
     * Before-prayer poster window anchored to JAMAAT:
     *   start = jamaat − minutes_before
     *   end   = jamaat
     *
     * Always a positive-length interval when minutes_before >= 1.
     * Countdown windows may still override display inside this range.
     *
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon, reference: string}|null
     */
    public static function beforePrayerPosterWindow(
        MediaSchedule $schedule,
        PrayerTime $prayerTimes,
        ?Carbon $referenceNow = null
    ): ?array {
        if ($schedule->schedule_type !== 'minutes_before_prayer'
            || !$schedule->prayer_name
            || $schedule->minutes_before_prayer === null
            || (int) $schedule->minutes_before_prayer < 1) {
            return null;
        }

        $jamaat = self::resolve($prayerTimes, $schedule->prayer_name, $referenceNow);
        if (!$jamaat) {
            return null;
        }

        $minutesBefore = (int) $schedule->minutes_before_prayer;
        $start = $jamaat->copy()->subMinutes($minutesBefore);
        $end = $jamaat->copy();

        return [
            'jamaat' => $jamaat->copy(),
            'start' => $start,
            'end' => $end,
            'reference' => 'jamaat',
        ];
    }

    /**
     * After-prayer poster window anchored to JAMAAT:
     *   start = jamaat + minutes_after
     *   end   = start + AFTER_POSTER_WINDOW_MINUTES
     *
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon, reference: string}|null
     */
    public static function afterPrayerPosterWindow(
        MediaSchedule $schedule,
        PrayerTime $prayerTimes,
        ?Carbon $referenceNow = null
    ): ?array {
        if ($schedule->schedule_type !== 'minutes_after_prayer'
            || !$schedule->prayer_name
            || $schedule->minutes_after_prayer === null
            || (int) $schedule->minutes_after_prayer < 1) {
            return null;
        }

        $jamaat = self::resolve($prayerTimes, $schedule->prayer_name, $referenceNow);
        if (!$jamaat) {
            return null;
        }

        $minutesAfter = (int) $schedule->minutes_after_prayer;
        $start = $jamaat->copy()->addMinutes($minutesAfter);
        $end = $start->copy()->addMinutes(self::AFTER_POSTER_WINDOW_MINUTES);

        return [
            'jamaat' => $jamaat->copy(),
            'start' => $start,
            'end' => $end,
            'reference' => 'jamaat',
        ];
    }

    /**
     * Inclusive window check in the mosque timezone: start <= now <= end.
     */
    public static function isWithinWindow(Carbon $now, Carbon $start, Carbon $end): bool
    {
        $tz = self::appTimezone();
        $now = $now->copy()->timezone($tz);
        $start = $start->copy()->timezone($tz);
        $end = $end->copy()->timezone($tz);

        if ($start->gt($end)) {
            return false;
        }

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
            // TIME values are clock-of-day, not absolute instants — do not shift by timezone.
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
            // Clock-only strings must not depend on the server/PHP default timezone.
            return Carbon::parse($trimmed, self::appTimezone())->format('H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    private static function resolvePrayerDate(PrayerTime $prayerTimes, Carbon $referenceNow): string
    {
        $raw = $prayerTimes->getRawOriginal('date') ?? $prayerTimes->getAttributes()['date'] ?? null;
        if (is_string($raw) && $raw !== '') {
            return Carbon::parse($raw)->toDateString();
        }

        if ($prayerTimes->date) {
            return Carbon::parse($prayerTimes->date)->toDateString();
        }

        return $referenceNow->copy()->timezone(self::appTimezone())->toDateString();
    }
}
