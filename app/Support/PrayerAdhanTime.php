<?php

namespace App\Support;

use App\Models\PrayerTime;
use Carbon\Carbon;

/**
 * Resolve adhan clock times from the uploaded timetable row.
 *
 * Priority:
 * 1) Explicit {prayer}_adhan column
 * 2) Beginning time ({prayer}) when adhan column is empty (matches display fallback)
 */
class PrayerAdhanTime
{
    public static function resolve(PrayerTime $prayerTimes, string $prayerName, ?Carbon $referenceNow = null): ?Carbon
    {
        $referenceNow = PrayerJamaatTime::now($referenceNow);
        $date = self::resolvePrayerDate($prayerTimes, $referenceNow);

        $adhanField = $prayerName . '_adhan';
        $explicitAdhan = PrayerJamaatTime::normalizeClockValue($prayerTimes->$adhanField ?? null);
        if ($explicitAdhan !== null) {
            return PrayerJamaatTime::parseClockOnDate($date, $explicitAdhan);
        }

        $beginning = PrayerJamaatTime::normalizeClockValue($prayerTimes->$prayerName ?? null);
        if ($beginning === null) {
            return null;
        }

        return PrayerJamaatTime::parseClockOnDate($date, $beginning);
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

        return $referenceNow->copy()->timezone(PrayerJamaatTime::appTimezone())->toDateString();
    }
}
