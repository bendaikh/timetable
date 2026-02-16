<?php

namespace App\Services;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use Carbon\Carbon;

class MediaDisplayService
{
    /**
     * Get the current media to display based on schedules and prayer times
     * Returns array with media and display info, or null if no media should be displayed (show timetable)
     */
    public function getCurrentMedia(): ?array
    {
        $now = Carbon::now();
        
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

        // Find which media should be displayed based on priority order and duration
        // Media 1: 0-29s, Media 2: 30-59s, etc.
        $accumulatedDuration = 0;
        foreach ($activeMediaItems as $media) {
            // Convert pivot duration from minutes to seconds
            $mediaDuration = $media->pivot->duration * 60;
            $mediaEndTime = $accumulatedDuration + $mediaDuration;
            
            // Check if current time falls within this media's time window
            if ($elapsedSeconds >= $accumulatedDuration && $elapsedSeconds < $mediaEndTime) {
                return [
                    'media' => $media,
                    'duration' => $mediaDuration,
                    'priority' => $media->pivot->priority,
                    'schedule' => $schedule
                ];
            }
            
            $accumulatedDuration = $mediaEndTime;
        }

        // If we've gone through all media, return null (schedule has ended)
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
     * Get countdown information for next prayer
     */
    public function getCountdownInfo(): ?array
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        
        $prayerTimes = PrayerTime::whereDate('date', $today)->first();
        
        if (!$prayerTimes) {
            return null;
        }

        $nextPrayer = null;
        $nextPrayerTime = null;

        foreach (['fajr', 'zohar', 'asr', 'maghrib', 'isha'] as $name) {
            $candidateTime = $this->resolveNextCountdownTarget($prayerTimes, $name, $now);
            if (!$candidateTime) {
                continue;
            }

            if (!$nextPrayerTime || $candidateTime->lt($nextPrayerTime)) {
                $nextPrayer = $name;
                $nextPrayerTime = $candidateTime;
            }
        }

        if (!$nextPrayer || !$nextPrayerTime) {
            return null;
        }

        $countdownDuration = (int) Setting::get('adhan_countdown_duration', 30);
        $countdownStart = $nextPrayerTime->copy()->subSeconds($countdownDuration);

        return [
            'prayer_name' => ucfirst($nextPrayer),
            'prayer_time' => $nextPrayerTime,
            'countdown_start' => $countdownStart,
            'countdown_duration' => $countdownDuration,
            'is_countdown_time' => $now->between($countdownStart, $nextPrayerTime)
        ];
    }

    private function resolveNextCountdownTarget(PrayerTime $prayerTimes, string $prayerName, Carbon $now): ?Carbon
    {
        $candidates = [];

        $adhanField = $prayerName . '_adhan';
        $adhanTime = $prayerTimes->$adhanField ?? null;
        if (is_string($adhanTime) && trim($adhanTime) !== '') {
            $candidates[] = Carbon::parse($adhanTime);
        }

        $jamaatField = $prayerName . '_jamaat';
        $jamaatTime = $prayerTimes->$jamaatField ?? null;
        if (is_string($jamaatTime) && trim($jamaatTime) !== '') {
            $candidates[] = Carbon::parse($jamaatTime);
        }

        $baseTime = $prayerTimes->$prayerName ?? null;
        if (is_string($baseTime) && trim($baseTime) !== '') {
            $candidates[] = Carbon::parse($baseTime);
        }

        $nextCandidate = null;
        foreach ($candidates as $candidate) {
            if ($candidate->isToday() && $candidate->gt($now)) {
                if (!$nextCandidate || $candidate->lt($nextCandidate)) {
                    $nextCandidate = $candidate;
                }
            }
        }

        return $nextCandidate;
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
    public function isAdhanOrCountdownActive(?Carbon $now = null): bool
    {
        if (!$now) {
            $now = Carbon::now();
        }

        $countdownInfo = $this->getCountdownInfo();
        
        // If no prayer times exist, countdown can't be active
        if (!$countdownInfo) {
            return false;
        }

        // Check if we're in countdown or adhan window
        // Countdown: from (prayer_time - duration) until prayer_time
        // Adhan: from prayer_time until (prayer_time + 5 seconds)
        $prayerTime = $countdownInfo['prayer_time'];
        $countdownStart = $countdownInfo['countdown_start'];
        $adhanEnd = $prayerTime->copy()->addSeconds(5);

        // Check if current time is between countdown start and adhan end
        return $now->between($countdownStart, $adhanEnd);
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
