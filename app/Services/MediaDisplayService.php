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
     * Returns null if no media should be displayed (show timetable)
     */
    public function getCurrentMedia(): ?Media
    {
        $now = Carbon::now();
        
        // Get all active schedules ordered by priority
        $schedules = MediaSchedule::with('media')
            ->active()
            ->whereHas('media', function($query) {
                $query->where('is_active', true);
            })
            ->orderedByPriority()
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->isActiveForToday()) {
                continue;
            }

            if ($this->shouldDisplayMedia($schedule, $now)) {
                return $schedule->media;
            }
        }

        // No scheduled media to display - show timetable
        return null;
    }

    /**
     * Check if a media should be displayed based on schedule type and current time
     */
    private function shouldDisplayMedia(MediaSchedule $schedule, Carbon $now): bool
    {
        switch ($schedule->schedule_type) {
            case 'minutes_before_prayer':
                return $schedule->isActiveForMinutesBeforePrayer();
            case 'minutes_after_prayer':
                return $schedule->isActiveForMinutesAfterPrayer();

            default:
                return false;
        }
    }


    /**
     * Get prayer time Carbon instance for today
     */
    private function getPrayerTime(string $prayerName): ?Carbon
    {
        $today = Carbon::now()->format('Y-m-d');
        $prayerTimes = PrayerTime::whereDate('date', $today)->first();
        
        if (!$prayerTimes) {
            return null;
        }

        $timeString = match($prayerName) {
            'fajr' => $prayerTimes->fajr,
            'zohar' => $prayerTimes->zohar,
            'asr' => $prayerTimes->asr,
            'maghrib' => $prayerTimes->maghrib,
            'isha' => $prayerTimes->isha,
            default => null
        };

        return $timeString ? Carbon::parse($timeString) : null;
    }

    /**
     * Get slideshow information for current media
     */
    public function getSlideshowInfo(): array
    {
        $now = Carbon::now();
        $currentMedia = $this->getCurrentMedia();
        
        if (!$currentMedia) {
            return [
                'should_display' => false,
                'media' => null,
                'duration' => 0,
                'next_schedule' => null
            ];
        }

        // Get the schedule that's currently active
        $activeSchedule = MediaSchedule::with('media')
            ->active()
            ->whereHas('media', function($query) {
                $query->where('is_active', true);
            })
            ->orderedByPriority()
            ->where('media_id', $currentMedia->id)
            ->first();

        $duration = $activeSchedule ? $activeSchedule->media->display_duration : 30;

        return [
            'should_display' => true,
            'media' => $currentMedia,
            'duration' => $duration,
            'schedule' => $activeSchedule,
            'next_schedule' => $this->getNextScheduledMedia()
        ];
    }

    /**
     * Get the next scheduled media in queue
     */
    private function getNextScheduledMedia(): ?MediaSchedule
    {
        $now = Carbon::now();
        
        $schedules = MediaSchedule::with('media')
            ->active()
            ->whereHas('media', function($query) {
                $query->where('is_active', true);
            })
            ->orderedByPriority()
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->isActiveForToday()) {
                continue;
            }

            // Check if this schedule will be active in the future
            if ($this->willBeActiveInFuture($schedule, $now)) {
                return $schedule;
            }
        }

        return null;
    }

    /**
     * Check if a schedule will be active in the future
     */
    private function willBeActiveInFuture(MediaSchedule $schedule, Carbon $now): bool
    {
        switch ($schedule->schedule_type) {
            case 'minutes_before_prayer':
                $startTime = $schedule->getDisplayStartTime();
                return $startTime && $startTime->gt($now);
            case 'minutes_after_prayer':
                $startTime = $schedule->getDisplayStartTime();
                return $startTime && $startTime->gt($now);

            default:
                return false;
        }
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

        $prayers = [
            'fajr' => $prayerTimes->fajr,
            'zohar' => $prayerTimes->zohar,
            'asr' => $prayerTimes->asr,
            'maghrib' => $prayerTimes->maghrib,
            'isha' => $prayerTimes->isha,
        ];

        $nextPrayer = null;
        $nextPrayerTime = null;

        foreach ($prayers as $name => $time) {
            $prayerTime = Carbon::parse($time);
            
            // If prayer time is today and hasn't passed yet
            if ($prayerTime->isToday() && $prayerTime->gt($now)) {
                if (!$nextPrayerTime || $prayerTime->lt($nextPrayerTime)) {
                    $nextPrayer = $name;
                    $nextPrayerTime = $prayerTime;
                }
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

    /**
     * Check if media display is enabled
     */
    public function isMediaDisplayEnabled(): bool
    {
        return (bool) Setting::get('media_display_enabled', true);
    }

    /**
     * Get all active media schedules for debugging
     */
    public function getActiveSchedules(): array
    {
        return MediaSchedule::with('media')
            ->where('is_active', true)
            ->whereHas('media', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('priority', 'desc')
            ->get()
            ->toArray();
    }
}
