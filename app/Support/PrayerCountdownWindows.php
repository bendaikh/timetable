<?php

namespace App\Support;

use Carbon\Carbon;

class PrayerCountdownWindows
{
    public const ADHAN_LEAD_SECONDS = 1200;
    public const IQAMAH_LEAD_SECONDS = 30;
    public const DURATION_SECONDS = 30;

    /**
     * Both countdown windows are anchored to iqamah (jamaat) time only:
     * - adhan phase: [iqamah - 20m, iqamah - 20m + 30s)
     * - iqamah phase: [iqamah - 30s, iqamah)
     */
    public static function resolveActivePhase(Carbon $now, Carbon $iqamahTime): ?string
    {
        $iqamahCountdownStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);
        if ($now->gte($iqamahCountdownStart) && $now->lt($iqamahTime)) {
            return 'iqamah';
        }

        $adhanCountdownStart = $iqamahTime->copy()->subSeconds(self::ADHAN_LEAD_SECONDS);
        $adhanCountdownEnd = $adhanCountdownStart->copy()->addSeconds(self::DURATION_SECONDS);
        if ($now->gte($adhanCountdownStart) && $now->lt($adhanCountdownEnd)) {
            return 'adhan';
        }

        return null;
    }

    public static function buildPayload(string $prayerName, Carbon $iqamahTime, string $phase, Carbon $now): array
    {
        if ($phase === 'iqamah') {
            $countdownStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);
            $countdownEnd = $iqamahTime->copy();
            $message = 'Iqamah will start in 30 seconds';
        } else {
            $countdownStart = $iqamahTime->copy()->subSeconds(self::ADHAN_LEAD_SECONDS);
            $countdownEnd = $countdownStart->copy()->addSeconds(self::DURATION_SECONDS);
            $message = 'Adhan will start in 30 seconds';
        }

        $secondsRemaining = max(0, (int) $now->diffInSeconds($countdownEnd, false));

        return [
            'phase' => $phase,
            'prayer_name' => ucfirst($prayerName),
            'target_field' => 'jamaat',
            'target_time' => $iqamahTime,
            'iqamah_time' => $iqamahTime,
            'countdown_start' => $countdownStart,
            'countdown_end' => $countdownEnd,
            'countdown_duration' => self::DURATION_SECONDS,
            'seconds_remaining' => $secondsRemaining,
            'message' => $message,
            'is_countdown_time' => true,
            'prayer_time' => $countdownEnd,
        ];
    }

    public static function windowSchedule(Carbon $iqamahTime): array
    {
        $adhanStart = $iqamahTime->copy()->subSeconds(self::ADHAN_LEAD_SECONDS);
        $iqamahStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);

        return [
            'adhan_countdown' => [
                'target_field' => 'jamaat',
                'target_time' => $iqamahTime->toIso8601String(),
                'start' => $adhanStart->toIso8601String(),
                'end' => $adhanStart->copy()->addSeconds(self::DURATION_SECONDS)->toIso8601String(),
                'duration_seconds' => self::DURATION_SECONDS,
                'message' => 'Adhan will start in 30 seconds',
            ],
            'iqamah_countdown' => [
                'target_field' => 'jamaat',
                'target_time' => $iqamahTime->toIso8601String(),
                'start' => $iqamahStart->toIso8601String(),
                'end' => $iqamahTime->toIso8601String(),
                'duration_seconds' => self::DURATION_SECONDS,
                'message' => 'Iqamah will start in 30 seconds',
            ],
        ];
    }
}
