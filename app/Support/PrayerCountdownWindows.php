<?php

namespace App\Support;

use Carbon\Carbon;

class PrayerCountdownWindows
{
    public const IQAMAH_LEAD_SECONDS = 30;
    public const DURATION_SECONDS = 30;

    /**
     * Green popup before adhan: [adhan - 30s, adhan).
     * Skipped when adhan and jamaat are the same clock time.
     */
    public static function resolveAdhanPopupPhase(Carbon $now, Carbon $adhanTime, ?Carbon $iqamahTime = null): ?string
    {
        if ($iqamahTime !== null && self::clockTimesEqual($adhanTime, $iqamahTime)) {
            return null;
        }

        $countdownStart = $adhanTime->copy()->subSeconds(self::DURATION_SECONDS);
        if ($now->gte($countdownStart) && $now->lt($adhanTime)) {
            return 'adhan';
        }

        return null;
    }

    /**
     * Green popup before jamaat: [iqamah - 30s, iqamah).
     */
    public static function resolveJamaatPopupPhase(Carbon $now, Carbon $iqamahTime): ?string
    {
        $iqamahCountdownStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);
        if ($now->gte($iqamahCountdownStart) && $now->lt($iqamahTime)) {
            return 'iqamah';
        }

        return null;
    }

    /**
     * Active popup phase for diagnostics: iqamah window wins when both could apply.
     */
    public static function resolveActivePhase(Carbon $now, Carbon $iqamahTime, ?Carbon $adhanTime = null): ?string
    {
        $jamaatPhase = self::resolveJamaatPopupPhase($now, $iqamahTime);
        if ($jamaatPhase !== null) {
            return $jamaatPhase;
        }

        if ($adhanTime === null) {
            return null;
        }

        return self::resolveAdhanPopupPhase($now, $adhanTime, $iqamahTime);
    }

    public static function buildPayload(
        string $prayerName,
        string $phase,
        Carbon $now,
        Carbon $iqamahTime,
        ?Carbon $adhanTime = null
    ): array {
        if ($phase === 'iqamah') {
            $countdownStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);
            $countdownEnd = $iqamahTime->copy();
            $message = 'Iqamah will start in 30 seconds';
            $targetField = 'jamaat';
            $targetTime = $iqamahTime;
        } else {
            $targetTime = $adhanTime ?? $iqamahTime;
            $countdownStart = $targetTime->copy()->subSeconds(self::DURATION_SECONDS);
            $countdownEnd = $targetTime->copy();
            $message = 'Adhan will start in 30 seconds';
            $targetField = 'adhan';
        }

        $secondsRemaining = max(0, (int) $now->diffInSeconds($countdownEnd, false));

        return [
            'phase' => $phase,
            'prayer_name' => ucfirst($prayerName),
            'target_field' => $targetField,
            'target_time' => $targetTime,
            'adhan_time' => $adhanTime,
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

    public static function windowSchedule(Carbon $iqamahTime, ?Carbon $adhanTime = null): array
    {
        $iqamahStart = $iqamahTime->copy()->subSeconds(self::IQAMAH_LEAD_SECONDS);

        $adhanCountdown = null;
        if ($adhanTime !== null && !self::clockTimesEqual($adhanTime, $iqamahTime)) {
            $adhanStart = $adhanTime->copy()->subSeconds(self::DURATION_SECONDS);
            $adhanCountdown = [
                'target_field' => 'adhan',
                'target_time' => $adhanTime->toIso8601String(),
                'start' => $adhanStart->toIso8601String(),
                'end' => $adhanTime->toIso8601String(),
                'duration_seconds' => self::DURATION_SECONDS,
                'message' => 'Adhan will start in 30 seconds',
            ];
        }

        return [
            'adhan_countdown' => $adhanCountdown,
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

    public static function clockTimesEqual(Carbon $left, Carbon $right): bool
    {
        return $left->format('H:i:s') === $right->format('H:i:s');
    }
}
