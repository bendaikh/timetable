<?php

namespace App\Support;

use App\Support\ScheduledMediaWindow;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduledMediaWindow
{
    /**
     * Whether pivot media is within its optional start/end window.
     * Null start or end parts mean "no restriction" for backward compatibility.
     */
    public static function isEligible(object $pivot, Carbon $now, string $timezone): bool
    {
        $start = self::combine($pivot->start_date ?? null, $pivot->start_time ?? null, $timezone);
        if ($start && $now->lt($start)) {
            return false;
        }

        $end = self::combine($pivot->expiry_date ?? null, $pivot->expiry_time ?? null, $timezone);
        if ($end && $now->greaterThanOrEqualTo($end)) {
            return false;
        }

        return true;
    }

    public static function combine(?string $date, ?string $time, string $timezone): ?Carbon
    {
        if (!$date || !$time) {
            return null;
        }

        $normalizedTime = strlen($time) === 5 ? $time . ':00' : $time;

        return Carbon::parse($date . ' ' . $normalizedTime, $timezone);
    }

    /**
     * @param  array<int, mixed>  $mediaIds
     * @throws ValidationException
     */
    public static function validateRequestWindows(
        array $mediaIds,
        ?array $startDates,
        ?array $startTimes,
        ?array $endDates,
        ?array $endTimes,
        string $timezone
    ): void {
        $startDates = $startDates ?? [];
        $startTimes = $startTimes ?? [];
        $endDates = $endDates ?? [];
        $endTimes = $endTimes ?? [];
        $errors = [];

        foreach ($mediaIds as $index => $mediaId) {
            $label = is_numeric($mediaId) ? 'media item #' . ((int) $index + 1) : 'media item';

            $startDate = $startDates[$index] ?? null;
            $startTime = $startTimes[$index] ?? null;
            $endDate = $endDates[$index] ?? null;
            $endTime = $endTimes[$index] ?? null;

            $hasStartDate = filled($startDate);
            $hasStartTime = filled($startTime);
            $hasEndDate = filled($endDate);
            $hasEndTime = filled($endTime);

            if ($hasStartDate xor $hasStartTime) {
                $errors["media_start_dates.$index"] = "Start date and start time must both be set for $label.";
            }

            if ($hasEndDate xor $hasEndTime) {
                $errors["media_expiry_dates.$index"] = "End date and end time must both be set for $label.";
            }

            if ($hasStartDate && $hasStartTime && $hasEndDate && $hasEndTime) {
                $start = self::combine($startDate, $startTime, $timezone);
                $end = self::combine($endDate, $endTime, $timezone);

                if ($start && $end && $start->greaterThanOrEqualTo($end)) {
                    $errors["media_start_dates.$index"] = "Start date/time must be before end date/time for $label.";
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
