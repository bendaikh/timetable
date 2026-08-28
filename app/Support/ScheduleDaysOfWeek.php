<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * ISO weekday numbers: 1 = Monday … 7 = Sunday (matches Carbon::dayOfWeekIso).
 */
class ScheduleDaysOfWeek
{
    public const LABELS = [
        1 => 'Mon',
        2 => 'Tue',
        3 => 'Wed',
        4 => 'Thu',
        5 => 'Fri',
        6 => 'Sat',
        7 => 'Sun',
    ];

    /**
     * @return list<int>|null Null means active every day.
     */
    public static function normalize(mixed $days): ?array
    {
        if ($days === null || $days === '') {
            return null;
        }

        if (is_string($days)) {
            $decoded = json_decode($days, true);
            $days = is_array($decoded) ? $decoded : null;
        }

        if (!is_array($days) || $days === []) {
            return null;
        }

        $normalized = array_values(array_unique(array_map(
            static fn ($day) => (int) $day,
            $days
        )));

        $normalized = array_values(array_filter(
            $normalized,
            static fn (int $day) => $day >= 1 && $day <= 7
        ));

        sort($normalized);

        return $normalized === [] ? null : $normalized;
    }

    public static function isActiveToday(mixed $days, Carbon $now): bool
    {
        $normalized = self::normalize($days);

        if ($normalized === null) {
            return true;
        }

        return in_array((int) $now->dayOfWeekIso, $normalized, true);
    }

    /**
     * @return list<int>|null
     */
    public static function normalizeFromRequest(?array $days): ?array
    {
        return self::normalize($days);
    }

    public static function formatLabels(mixed $days): string
    {
        $normalized = self::normalize($days);

        if ($normalized === null) {
            return 'All Days';
        }

        return collect($normalized)
            ->map(fn (int $day) => self::LABELS[$day] ?? (string) $day)
            ->join(', ');
    }
}
