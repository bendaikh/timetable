<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Support\PrayerCountdownWindows;
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

        // Get all active schedules ordered by ID (creation order)
        $schedules = MediaSchedule::with(['mediaItems' => function($query) {
                $query->where('media_schedule_media.is_active', true)
                      ->orderBy('media_schedule_media.priority', 'asc');
            }])
            ->active()
            ->whereHas('mediaItems', function($query) {
                $query->where('media_schedule_media.is_active', true);
            })
            ->orderBy('id', 'desc')
            ->get();

        // Priority 1: Check for Before/After Prayer schedules first (they override Full Time Poster)
        $prayerBasedMedia = null;
        $fullTimePosterMedia = null;
        
        foreach ($schedules as $schedule) {
            // Check if schedule is active for today (schedule level check still applies)
            if (!$schedule->isActiveForToday()) {
                continue;
            }

            $mediaInfo = $this->shouldDisplayMediaFromSchedule($schedule, $now);
            
            if ($mediaInfo) {
                // Categorize by schedule type
                if ($schedule->schedule_type === 'full_time_poster') {
                    // Store Full Time Poster as fallback
                    if (!$fullTimePosterMedia) {
                        $fullTimePosterMedia = $mediaInfo;
                    }
                } else {
                    // Before/After Prayer schedules have priority
                    $prayerBasedMedia = $mediaInfo;
                    break; // Found a prayer-based schedule, stop looking
                }
            }
        }

        // Return prayer-based media if found, otherwise full-time poster, otherwise null (timetable)
        return $prayerBasedMedia ?? $fullTimePosterMedia;
    }

    /**
     * Check if any media from this schedule should be displayed
     * Returns array with media and display info if should display, null otherwise
     */
    private function shouldDisplayMediaFromSchedule(MediaSchedule $schedule, Carbon $now): ?array
    {
        // Check if schedule is active based on type
        if (!$this->isScheduleActive($schedule, $now)) {
            return null;
        }

        // Get the start time of the schedule
        $scheduleStart = $this->getScheduleStartTime($schedule);
        if (!$scheduleStart) {
            return null;
        }

        // For full_time_poster, cycle through media continuously
        if ($schedule->schedule_type === 'full_time_poster') {
            return $this->getFullTimePosterMedia($schedule, $now, $scheduleStart);
        }

        // For prayer-based schedules, check if we're within the display window
        $scheduleEnd = $this->getScheduleEndTime($schedule);
        if (!$scheduleEnd || !$now->between($scheduleStart, $scheduleEnd)) {
            return null;
        }

        // Calculate which media should be displayed based on time elapsed
        return $this->getMediaFromSequence($schedule, $now, $scheduleStart);
    }

    /**
     * Check if schedule should be active right now
     */
    private function isScheduleActive(MediaSchedule $schedule, Carbon $now): bool
    {
        switch ($schedule->schedule_type) {
            case 'minutes_before_prayer':
                return $schedule->isActiveForMinutesBeforePrayer();
            case 'minutes_after_prayer':
                return $schedule->isActiveForMinutesAfterPrayer();
            case 'full_time_poster':
                return true; // Always active
            default:
                return false;
        }
    }

    /**
     * Get schedule start time
     */
    private function getScheduleStartTime(MediaSchedule $schedule): ?Carbon
    {
        if ($schedule->schedule_type === 'full_time_poster') {
            // Start from beginning of today
            return Carbon::today();
        }
        
        return $schedule->getDisplayStartTime();
    }

    /**
     * Get schedule end time
     */
    private function getScheduleEndTime(MediaSchedule $schedule): ?Carbon
    {
        if ($schedule->schedule_type === 'full_time_poster') {
            // End at end of today
            return Carbon::today()->endOfDay();
        }
        
        return $schedule->getDisplayEndTime();
    }

    /**
     * Get media for full time poster (cycles continuously)
     * 
     * Handles gap durations:
     * - Each media has a configurable gap duration after it plays
     * - Gap=0 means back-to-back continuous display (no interruption)
     * - Gap>0 means pause before next media
     * - Cycle continues indefinitely with all gaps respected
     */
    private function getFullTimePosterMedia(MediaSchedule $schedule, Carbon $now, Carbon $scheduleStart): ?array
    {
        $mediaItems = $schedule->mediaItems;
        if ($mediaItems->isEmpty()) {
            return null;
        }

        // Filter out expired media and media not active for today
        $activeMediaItems = $mediaItems->filter(function($media) use ($now) {
            // Check expiry date/time
            if ($media->pivot->expiry_date && $media->pivot->expiry_time) {
                $expiryDateTime = Carbon::parse($media->pivot->expiry_date . ' ' . $media->pivot->expiry_time);
                if ($now->greaterThanOrEqualTo($expiryDateTime)) {
                    return false; // Media has expired
                }
            }
            
            // Check days of week for this media
            if ($media->pivot->days_of_week) {
                $daysOfWeek = is_string($media->pivot->days_of_week) 
                    ? json_decode($media->pivot->days_of_week, true) 
                    : $media->pivot->days_of_week;
                    
                if (!empty($daysOfWeek)) {
                    $today = $now->dayOfWeekIso; // 1-7 (Monday-Sunday)
                    if (!in_array($today, $daysOfWeek)) {
                        return false; // Media not active for today
                    }
                }
            }
            
            return true; // Media is active
        });

        if ($activeMediaItems->isEmpty()) {
            return null;
        }

        // Calculate total cycle duration (media duration + gap duration for each media)
        // Convert pivot duration from minutes to seconds
        $totalDuration = $activeMediaItems->sum(function($media) {
            return ($media->pivot->duration * 60) + ($media->pivot->gap_duration ?? 0);
        });
        
        if ($totalDuration <= 0) {
            return null;
        }

        // Calculate seconds elapsed since start of day
        // Use diffInSeconds with false to get signed difference
        $elapsedSeconds = (int)$scheduleStart->diffInSeconds($now, false);
        
        // For full time poster, ensure we have a positive elapsed time
        if ($elapsedSeconds < 0) {
            $elapsedSeconds = 0;
        }
        
        // Find position within current cycle
        $positionInCycle = $elapsedSeconds % $totalDuration;

        // Find which media should be displayed
        // Account for gap durations: media plays until (duration), then gap period before next media
        $accumulatedDuration = 0;
        foreach ($activeMediaItems as $media) {
            // Convert pivot duration from minutes to seconds
            $mediaDuration = $media->pivot->duration * 60;
            $gapDuration = $media->pivot->gap_duration ?? 0;
            $totalSlotDuration = $mediaDuration + $gapDuration;
            
            // Check if current time falls within this media's display window (not during gap)
            if ($positionInCycle >= $accumulatedDuration && $positionInCycle < ($accumulatedDuration + $mediaDuration)) {
                // We're in the media display period
                return [
                    'media' => $media,
                    'duration' => $mediaDuration,
                    'priority' => $media->pivot->priority,
                    'schedule' => $schedule
                ];
            }
            
            // If we're in the gap period (after media, before next media), 
            // we want to show the next media immediately or return timetable if that's desired
            // For now, gap periods show nothing (returns null to display timetable)
            // This can be enhanced in future to show next media or loading state
            if ($positionInCycle >= ($accumulatedDuration + $mediaDuration) && $positionInCycle < ($accumulatedDuration + $totalSlotDuration)) {
                // We're in a gap period - return null to show main screen during gap
                // Admin can set gap=0 for back-to-back continuous display
                return null;
            }
            
            $accumulatedDuration += $totalSlotDuration;
        }

        // If we've gone through all media without finding a match, return null
        // This shouldn't happen if logic is correct, but serve as fallback
        return null;
    }

    /**
     * Get media from sequence based on elapsed time
     */
    private function getMediaFromSequence(MediaSchedule $schedule, Carbon $now, Carbon $scheduleStart): ?array
    {
        $mediaItems = $schedule->mediaItems;
        if ($mediaItems->isEmpty()) {
            return null;
        }

        // Filter media based on days_of_week at media level
        $activeMediaItems = $mediaItems->filter(function($media) use ($now) {
            // Check days of week for this media
            if ($media->pivot->days_of_week) {
                $daysOfWeek = is_string($media->pivot->days_of_week) 
                    ? json_decode($media->pivot->days_of_week, true) 
                    : $media->pivot->days_of_week;
                    
                if (!empty($daysOfWeek)) {
                    $today = $now->dayOfWeekIso; // 1-7 (Monday-Sunday)
                    if (!in_array($today, $daysOfWeek)) {
                        return false; // Media not active for today
                    }
                }
            }
            
            return true; // Media is active
        });

        if ($activeMediaItems->isEmpty()) {
            return null;
        }

        // Calculate seconds elapsed since schedule start
        // Use diffInSeconds with false to get signed difference (can be negative if schedule hasn't started)
        $elapsedSeconds = $scheduleStart->diffInSeconds($now, false);
        
        // If schedule hasn't started yet, return null
        if ($elapsedSeconds < 0) {
            return null;
        }

        // Loop media for the entire schedule window (same approach as full-time posters).
        $totalCycleDuration = $activeMediaItems->sum(function ($media) {
            return ($media->pivot->duration * 60) + ($media->pivot->gap_duration ?? 0);
        });

        if ($totalCycleDuration <= 0) {
            return null;
        }

        $positionInCycle = $elapsedSeconds % $totalCycleDuration;

        $accumulatedDuration = 0;
        foreach ($activeMediaItems as $media) {
            $mediaDuration = $media->pivot->duration * 60;
            $gapDuration = $media->pivot->gap_duration ?? 0;
            $slotDuration = $mediaDuration + $gapDuration;

            if ($positionInCycle >= $accumulatedDuration && $positionInCycle < ($accumulatedDuration + $mediaDuration)) {
                return [
                    'media' => $media,
                    'duration' => $mediaDuration,
                    'priority' => $media->pivot->priority,
                    'schedule' => $schedule,
                ];
            }

            $accumulatedDuration += $slotDuration;
        }

        // In a configured gap between media items, show the timetable until the next item.
        return null;
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
     * Two windows only, both anchored to iqamah (jamaat) time:
     * - adhan:  [iqamah - 20m, iqamah - 20m + 30s)
     * - iqamah: [iqamah - 30s, iqamah)
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
            $iqamahTime = $this->resolveIqamahTime($prayerTimes, $name, $now);
            if (!$iqamahTime) {
                continue;
            }

            $phase = PrayerCountdownWindows::resolveActivePhase($now, $iqamahTime);
            if (!$phase) {
                continue;
            }

            $candidate = PrayerCountdownWindows::buildPayload($name, $iqamahTime, $phase, $now);
            if (!$activeCountdown || $iqamahTime->lt($activeCountdown['iqamah_time'])) {
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
                $iqamahTime = $this->resolveIqamahTime($prayerTimes, $name, $now);
                if (!$iqamahTime) {
                    continue;
                }

                $adhanTime = $this->resolveAdhanTime($prayerTimes, $name, $now);
                $phase = PrayerCountdownWindows::resolveActivePhase($now, $iqamahTime);
                $schedule = PrayerCountdownWindows::windowSchedule($iqamahTime);

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
        return (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
    }

    private function appTimezone(): string
    {
        return $this->getAppTimezone();
    }

    private function nowInAppTimezone(?Carbon $now = null): Carbon
    {
        return $now
            ? $now->copy()->timezone($this->appTimezone())
            : Carbon::now($this->appTimezone());
    }

    private function parsePrayerClockTime(string $date, ?string $time, Carbon $referenceNow): ?Carbon
    {
        if (!is_string($time) || trim($time) === '') {
            return null;
        }

        $normalized = strlen($time) === 5 ? $time . ':00' : $time;

        return Carbon::parse($date . ' ' . $normalized, $this->appTimezone());
    }

    private function resolveAdhanTime(PrayerTime $prayerTimes, string $prayerName, Carbon $referenceNow): ?Carbon
    {
        $adhanField = $prayerName . '_adhan';
        $adhanTime = $prayerTimes->$adhanField ?? null;

        return $this->parsePrayerClockTime(
            $referenceNow->toDateString(),
            is_string($adhanTime) ? $adhanTime : null,
            $referenceNow
        );
    }

    private function resolveIqamahTime(PrayerTime $prayerTimes, string $prayerName, Carbon $referenceNow): ?Carbon
    {
        $jamaatField = $prayerName . '_jamaat';
        $jamaatTime = $prayerTimes->$jamaatField ?? null;
        if (is_string($jamaatTime) && trim($jamaatTime) !== '') {
            return $this->parsePrayerClockTime($referenceNow->toDateString(), $jamaatTime, $referenceNow);
        }

        $baseTime = $prayerTimes->$prayerName ?? null;
        if (!is_string($baseTime) || trim($baseTime) === '') {
            return null;
        }

        $offsetMinutes = (int) Setting::get($prayerName . '_jamaat_offset', 0);

        return $this->parsePrayerClockTime($referenceNow->toDateString(), $baseTime, $referenceNow)
            ?->addMinutes($offsetMinutes);
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
}
