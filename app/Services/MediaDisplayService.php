<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Support\PrayerAdhanTime;
use App\Support\PrayerCountdownWindows;
use App\Support\PrayerJamaatTime;
use App\Support\ScheduledMediaWindow;
use App\Support\AnnouncementBoxGeometry;
use App\Support\MediaScheduleDuration;
use Carbon\Carbon;

class MediaDisplayService
{
    /**
     * Get the current media to display based on schedules and prayer times
     * Returns array with media and display info, or null if no media should be displayed (show timetable)
     */
    public function getCurrentMedia(): ?array
    {
        $now = $this->nowInAppTimezone();

        // Countdown windows always override posters (same rule as getScreenState()).
        if ($this->isAdhanOrCountdownActive($now)) {
            return null;
        }

        $schedules = MediaSchedule::with(['mediaItems' => function ($query) {
                $query->where('media_schedule_media.is_active', true)
                    ->orderBy('media_schedule_media.priority', 'asc')
                    ->orderBy('media_schedule_media.id', 'asc');
            }])
            ->active()
            ->whereHas('mediaItems', function ($query) {
                $query->where('media_schedule_media.is_active', true);
            })
            ->orderBy('id', 'asc')
            ->get();

        $prayerSchedules = [];
        $fullTimeSchedules = [];

        foreach ($schedules as $schedule) {
            if (!$schedule->isActiveForToday($now)) {
                continue;
            }

            if (!$this->isScheduleActive($schedule, $now)) {
                continue;
            }

            if ($schedule->schedule_type === 'full_time_poster') {
                $fullTimeSchedules[] = $schedule;
            } else {
                $prayerSchedules[] = $schedule;
            }
        }

        // Prayer-relative schedules override full-time, but ALL active prayer
        // schedules in-window contribute posters to one shared rotation.
        if ($prayerSchedules !== []) {
            return $this->pickFromMergedSchedules($prayerSchedules, $now, 'prayer');
        }

        if ($fullTimeSchedules !== []) {
            return $this->pickFromMergedSchedules($fullTimeSchedules, $now, 'full_time');
        }

        return null;
    }

    /**
     * Merge eligible media from every active schedule in the tier and cycle in priority order.
     *
     * @param  list<MediaSchedule>  $schedules
     */
    private function pickFromMergedSchedules(array $schedules, Carbon $now, string $tier): ?array
    {
        $items = collect();

        foreach ($schedules as $schedule) {
            foreach ($schedule->mediaItems as $media) {
                if (!$media->is_active) {
                    continue;
                }
                if (!$this->isPivotMediaEligible($media, $now)) {
                    continue;
                }
                if (!$this->isPivotMediaActiveForToday($media, $now)) {
                    continue;
                }

                // Keep schedule reference for payload / diagnostics.
                $media->setRelation('current_schedule', $schedule);
                $items->push($media);
            }
        }

        if ($items->isEmpty()) {
            return null;
        }

        // Same media attached to multiple schedules: keep the lowest priority entry.
        $items = $items
            ->groupBy(fn ($media) => (string) $media->id)
            ->map(function ($group) {
                return $group->sortBy(fn ($media) => sprintf(
                    '%05d-%05d',
                    (int) ($media->pivot->priority ?? 999),
                    (int) ($media->current_schedule->id ?? 0)
                ))->first();
            })
            // Single composite key: sortBy([...callables]) is comparator-style in Laravel.
            ->sortBy(fn ($media) => sprintf(
                '%05d-%05d-%05d',
                (int) ($media->pivot->priority ?? 999),
                (int) ($media->current_schedule->id ?? 0),
                (int) $media->id
            ))
            ->values();

        $scheduleStart = $tier === 'full_time'
            ? PrayerJamaatTime::now($now)->copy()->startOfDay()
            : $this->earliestScheduleStart($schedules, $now);

        if (!$scheduleStart) {
            return null;
        }

        return $this->pickMediaFromCycle($items, $now, $scheduleStart);
    }

    /**
     * @param  list<MediaSchedule>  $schedules
     */
    private function earliestScheduleStart(array $schedules, Carbon $now): ?Carbon
    {
        $starts = [];
        foreach ($schedules as $schedule) {
            $start = $this->getScheduleStartTime($schedule, $now);
            if ($start) {
                $starts[] = $start;
            }
        }

        if ($starts === []) {
            return null;
        }

        usort($starts, fn (Carbon $a, Carbon $b) => $a->timestamp <=> $b->timestamp);

        return $starts[0];
    }

    /**
     * Walk the ordered media list using elapsed seconds since scheduleStart.
     *
     * @param  \Illuminate\Support\Collection<int, Media>  $mediaItems
     */
    private function pickMediaFromCycle($mediaItems, Carbon $now, Carbon $scheduleStart): ?array
    {
        $totalDuration = $mediaItems->sum(function ($media) {
            return MediaScheduleDuration::secondsFromStored($media->pivot->duration)
                + (int) ($media->pivot->gap_duration ?? 0);
        });

        if ($totalDuration <= 0) {
            return null;
        }

        $elapsedSeconds = (int) $scheduleStart->diffInSeconds($now, false);
        if ($elapsedSeconds < 0) {
            return null;
        }

        $positionInCycle = $elapsedSeconds % $totalDuration;
        $accumulatedDuration = 0;

        foreach ($mediaItems as $media) {
            $mediaDuration = MediaScheduleDuration::secondsFromStored($media->pivot->duration);
            $gapDuration = (int) ($media->pivot->gap_duration ?? 0);
            $slotDuration = $mediaDuration + $gapDuration;

            if ($positionInCycle >= $accumulatedDuration && $positionInCycle < ($accumulatedDuration + $mediaDuration)) {
                $schedule = $media->relationLoaded('current_schedule')
                    ? $media->getRelation('current_schedule')
                    : null;

                return [
                    'media' => $media,
                    'duration' => $mediaDuration,
                    'priority' => $media->pivot->priority,
                    'schedule' => $schedule,
                ];
            }

            // Gap after this item: show timetable until the next slot.
            if (
                $gapDuration > 0
                && $positionInCycle >= ($accumulatedDuration + $mediaDuration)
                && $positionInCycle < ($accumulatedDuration + $slotDuration)
            ) {
                return null;
            }

            $accumulatedDuration += $slotDuration;
        }

        return null;
    }

    /**
     * Check if any media from this schedule should be displayed
     * Returns array with media and display info if should display, null otherwise
     *
     * @deprecated Internal path kept for diagnostics; getCurrentMedia merges schedules.
     */
    private function shouldDisplayMediaFromSchedule(MediaSchedule $schedule, Carbon $now): ?array
    {
        if (!$this->isScheduleActive($schedule, $now)) {
            return null;
        }

        return $this->pickFromMergedSchedules([$schedule], $now, $schedule->schedule_type === 'full_time_poster' ? 'full_time' : 'prayer');
    }

    /**
     * Check if schedule should be active right now
     */
    private function isScheduleActive(MediaSchedule $schedule, Carbon $now): bool
    {
        switch ($schedule->schedule_type) {
            case 'minutes_before_prayer':
                return $schedule->isActiveForMinutesBeforePrayer($now);
            case 'minutes_after_prayer':
                return $schedule->isActiveForMinutesAfterPrayer($now);
            case 'full_time_poster':
                return true; // Always active
            default:
                return false;
        }
    }

    /**
     * Get schedule start time
     */
    private function getScheduleStartTime(MediaSchedule $schedule, Carbon $now): ?Carbon
    {
        if ($schedule->schedule_type === 'full_time_poster') {
            // Start from beginning of today in app timezone
            return PrayerJamaatTime::now($now)->copy()->startOfDay();
        }

        return $schedule->getDisplayStartTime($now);
    }

    /**
     * Get schedule end time
     */
    private function getScheduleEndTime(MediaSchedule $schedule, Carbon $now): ?Carbon
    {
        if ($schedule->schedule_type === 'full_time_poster') {
            return PrayerJamaatTime::now($now)->copy()->endOfDay();
        }

        return $schedule->getDisplayEndTime($now);
    }

    /**
     * Get slideshow information for current media
     */
    public function getSlideshowInfo(): array
    {
        $mediaInfo = $this->getCurrentMedia();
        
        if (!$mediaInfo) {
            return [
                'should_display' => false,
                'media' => null,
                'duration' => 0,
                'next_schedule' => null
            ];
        }

        return [
            'should_display' => true,
            'media' => $mediaInfo['media'],
            'duration' => $mediaInfo['duration'],
            'priority' => $mediaInfo['priority'],
            'schedule' => $mediaInfo['schedule'],
            'next_schedule' => null // Can be enhanced later
        ];
    }

    /**
     * Get countdown information when a prayer countdown window is active.
     *
     * Green popup windows (30 seconds each):
     * - adhan:  [resolved adhan - 30s, adhan) — skipped when adhan == jamaat
     * - iqamah: [resolved jamaat - 30s, jamaat)
     */
    public function getCountdownInfo(?Carbon $now = null): ?array
    {
        $now = $this->nowInAppTimezone($now);
        $today = $now->toDateString();

        $prayerTimes = PrayerTime::whereDate('date', $today)->orderBy('id')->first();
        if (!$prayerTimes) {
            return null;
        }

        $activeCountdown = null;

        foreach (['fajr', 'zohar', 'asr', 'maghrib', 'isha'] as $name) {
            $iqamahTime = PrayerJamaatTime::resolve($prayerTimes, $name, $now);
            if (!$iqamahTime) {
                continue;
            }

            $adhanTime = PrayerAdhanTime::resolve($prayerTimes, $name, $now);

            $phase = PrayerCountdownWindows::resolveJamaatPopupPhase($now, $iqamahTime)
                ?? ($adhanTime
                    ? PrayerCountdownWindows::resolveAdhanPopupPhase($now, $adhanTime, $iqamahTime)
                    : null);

            if ($phase === null) {
                continue;
            }

            $candidate = PrayerCountdownWindows::buildPayload($name, $phase, $now, $iqamahTime, $adhanTime);
            if (!$activeCountdown || $candidate['countdown_end']->lt($activeCountdown['countdown_end'])) {
                $activeCountdown = $candidate;
            }
        }

        return $activeCountdown;
    }

    public function getCountdownDiagnostic(?Carbon $now = null): array
    {
        $now = $this->nowInAppTimezone($now);
        $today = $now->toDateString();
        $timezone = $this->appTimezone();

        $prayerTimes = PrayerTime::whereDate('date', $today)->orderBy('id')->first();
        $active = $this->getCountdownInfo($now);

        $prayers = [];
        if ($prayerTimes) {
            foreach (['fajr', 'zohar', 'asr', 'maghrib', 'isha'] as $name) {
                $iqamahTime = PrayerJamaatTime::resolve($prayerTimes, $name, $now);
                if (!$iqamahTime) {
                    continue;
                }

                $adhanTime = PrayerAdhanTime::resolve($prayerTimes, $name, $now);
                $phase = PrayerCountdownWindows::resolveActivePhase($now, $iqamahTime, $adhanTime);
                $schedule = PrayerCountdownWindows::windowSchedule($iqamahTime, $adhanTime);

                $prayers[] = [
                    'prayer' => $name,
                    'beginning_time' => $prayerTimes->$name,
                    'adhan_time' => $prayerTimes->{$name . '_adhan'} ?? null,
                    'jamaat_time' => $prayerTimes->{$name . '_jamaat'} ?? null,
                    'resolved_adhan_time' => $adhanTime?->toIso8601String(),
                    'resolved_jamaat_time' => $iqamahTime->toIso8601String(),
                    'windows' => $schedule,
                    'active_now' => $phase !== null,
                    'active_phase' => $phase,
                ];
            }
        }

        $log = [
            'server_time' => $now->toIso8601String(),
            'server_timezone' => $timezone,
            'prayer_date' => $today,
            'prayer_row_id' => $prayerTimes?->id,
            'countdown_active' => $active !== null,
            'target_prayer' => $active['prayer_name'] ?? null,
            'countdown_phase' => $active['phase'] ?? null,
            'target_field' => $active['target_field'] ?? null,
            'target_time' => isset($active['target_time']) ? $active['target_time']->toIso8601String() : null,
            'countdown_start' => isset($active['countdown_start']) ? $active['countdown_start']->toIso8601String() : null,
            'countdown_end' => isset($active['countdown_end']) ? $active['countdown_end']->toIso8601String() : null,
            'seconds_remaining' => $active['seconds_remaining'] ?? null,
            'message' => $active['message'] ?? null,
        ];

        return [
            'log' => $log,
            'prayers' => $prayers,
            'active_countdown' => $this->formatCountdownForApi($active),
            'screen_state' => $active ? 'COUNTDOWN' : 'TIMETABLE',
        ];
    }

    public function formatCountdownForApi(?array $countdown): ?array
    {
        if (!$countdown) {
            return null;
        }

        $format = static fn (?Carbon $value) => $value?->toIso8601String();

        return [
            'phase' => $countdown['phase'],
            'prayer_name' => $countdown['prayer_name'],
            'target_field' => $countdown['target_field'],
            'target_time' => $format($countdown['target_time'] ?? null),
            'adhan_time' => $format($countdown['adhan_time'] ?? null),
            'iqamah_time' => $format($countdown['iqamah_time'] ?? null),
            'countdown_start' => $format($countdown['countdown_start'] ?? null),
            'countdown_end' => $format($countdown['countdown_end'] ?? null),
            'countdown_duration' => $countdown['countdown_duration'],
            'seconds_remaining' => $countdown['seconds_remaining'],
            'message' => $countdown['message'],
            'is_countdown_time' => $countdown['is_countdown_time'],
            'prayer_time' => $format($countdown['prayer_time'] ?? null),
        ];
    }

    public function getAppTimezone(): string
    {
        return PrayerJamaatTime::appTimezone();
    }

    private function appTimezone(): string
    {
        return $this->getAppTimezone();
    }

    private function nowInAppTimezone(?Carbon $now = null): Carbon
    {
        return PrayerJamaatTime::now($now);
    }

    /**
     * Debug payload: mosque clock, resolved windows, and why a poster is/isn't active.
     */
    public function getPosterScheduleDiagnostic(?Carbon $now = null): array
    {
        $now = $this->nowInAppTimezone($now);
        $timezone = $this->appTimezone();
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        $countdownActive = $this->isAdhanOrCountdownActive($now);
        $currentMedia = $countdownActive ? null : $this->getCurrentMedia();

        $schedules = MediaSchedule::with(['mediaItems' => function ($query) {
                $query->where('media_schedule_media.is_active', true)
                    ->orderBy('media_schedule_media.priority', 'asc');
            }])
            ->active()
            ->orderBy('id', 'desc')
            ->get();

        $rows = [];
        foreach ($schedules as $schedule) {
            $window = null;
            if ($schedule->schedule_type === 'minutes_before_prayer') {
                $window = $schedule->resolveBeforePrayerWindow($now);
            } elseif ($schedule->schedule_type === 'minutes_after_prayer') {
                $window = $schedule->resolveAfterPrayerWindow($now);
            }

            $activeForToday = $schedule->isActiveForToday($now);
            $withinWindow = $window
                ? PrayerJamaatTime::isWithinWindow($now, $window['start'], $window['end'])
                : ($schedule->schedule_type === 'full_time_poster' && $activeForToday);

            $reason = 'inactive';
            if (!$activeForToday) {
                $reason = 'not_active_today';
            } elseif ($schedule->schedule_type === 'full_time_poster') {
                $reason = $countdownActive ? 'blocked_by_countdown' : 'full_time_eligible';
            } elseif (!$window) {
                $reason = 'no_prayer_times_or_invalid_schedule';
            } elseif ($countdownActive && $withinWindow) {
                $reason = 'in_window_but_blocked_by_countdown';
            } elseif ($withinWindow) {
                $reason = 'active_window';
            } elseif ($now->lt($window['start'])) {
                $reason = 'before_window_start';
            } else {
                $reason = 'after_window_end';
            }

            $rows[] = [
                'id' => $schedule->id,
                'schedule_type' => $schedule->schedule_type,
                'prayer_name' => $schedule->prayer_name,
                'minutes_before_prayer' => $schedule->minutes_before_prayer,
                'minutes_after_prayer' => $schedule->minutes_after_prayer,
                'is_active' => $schedule->is_active,
                'active_for_today' => $activeForToday,
                'jamaat_time' => isset($window['jamaat']) ? $window['jamaat']->toIso8601String() : null,
                'reference' => $window['reference'] ?? ($schedule->schedule_type === 'full_time_poster' ? null : 'jamaat'),
                'window_start' => isset($window['start']) ? $window['start']->toIso8601String() : null,
                'window_end' => isset($window['end']) ? $window['end']->toIso8601String() : null,
                'within_window_now' => $withinWindow,
                'status' => $reason,
                'media_count' => $schedule->mediaItems->count(),
            ];
        }

        return [
            'mosque_timezone' => $timezone,
            'php_timezone' => date_default_timezone_get(),
            'app_config_timezone' => (string) config('app.timezone'),
            'now' => $now->toIso8601String(),
            'prayer_date' => $now->toDateString(),
            'prayer_row_id' => $prayerTimes?->id,
            'countdown_blocking_posters' => $countdownActive,
            'current_media' => $currentMedia ? [
                'media_id' => $currentMedia['media']->id ?? null,
                'title' => $currentMedia['media']->title ?? null,
                'schedule_id' => $currentMedia['schedule']->id ?? null,
                'schedule_type' => $currentMedia['schedule']->schedule_type ?? null,
            ] : null,
            'schedules' => $rows,
            'poster_size' => AnnouncementBoxGeometry::recommendation(),
        ];
    }

    /**
     * Check if media display is enabled
     */
    public function isMediaDisplayEnabled(): bool
    {
        return (bool) Setting::get('media_display_enabled', true);
    }

    /**
     * Check if ADHAN or COUNTDOWN is currently active
     * This takes HIGHEST PRIORITY - blocks ALL media
     */
    public function currentTime(): Carbon
    {
        return $this->nowInAppTimezone();
    }

    public function isAdhanOrCountdownActive(?Carbon $now = null): bool
    {
        $countdownInfo = $this->getCountdownInfo($now);

        return $countdownInfo !== null && ($countdownInfo['is_countdown_time'] ?? false);
    }

    /**
     * Get all active media schedules for debugging
     */
    public function getActiveSchedules(): array
    {
        return MediaSchedule::with('mediaItems')
            ->where('is_active', true)
            ->whereHas('mediaItems', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('id', 'desc')
            ->get()
            ->toArray();
    }

    private function isPivotMediaEligible($media, Carbon $now): bool
    {
        return ScheduledMediaWindow::isEligible($media->pivot, $now, $this->appTimezone());
    }

    private function isPivotMediaActiveForToday($media, Carbon $now): bool
    {
        if (!$media->pivot->days_of_week) {
            return true;
        }

        $daysOfWeek = is_string($media->pivot->days_of_week)
            ? json_decode($media->pivot->days_of_week, true)
            : $media->pivot->days_of_week;

        if (empty($daysOfWeek)) {
            return true;
        }

        return in_array($now->dayOfWeekIso, $daysOfWeek, true);
    }
}
