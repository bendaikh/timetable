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
     */
    public function getCurrentMedia(): ?Media
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        
        // Get prayer times for today
        $prayerTimes = PrayerTime::whereDate('date', $today)->first();
        
        if (!$prayerTimes) {
            return null;
        }

        // Check for scheduled media based on priority
        $scheduledMedia = $this->getScheduledMedia($now, $prayerTimes);
        
        if ($scheduledMedia) {
            return $scheduledMedia;
        }

        // Fallback to default media (always display)
        return $this->getDefaultMedia();
    }

    /**
     * Get scheduled media based on current time and prayer times
     */
    private function getScheduledMedia(Carbon $now, PrayerTime $prayerTimes): ?Media
    {
        // Get all active schedules ordered by priority
        $schedules = MediaSchedule::with('media')
            ->where('is_active', true)
            ->whereHas('media', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('priority', 'desc')
            ->get();

        foreach ($schedules as $schedule) {
            if (!$schedule->isActiveForToday()) {
                continue;
            }

            switch ($schedule->schedule_type) {
                case 'prayer_before':
                    if ($this->isBeforePrayerTime($now, $schedule, $prayerTimes)) {
                        return $schedule->media;
                    }
                    break;

                case 'prayer_after':
                    if ($this->isAfterPrayerTime($now, $schedule, $prayerTimes)) {
                        return $schedule->media;
                    }
                    break;

                case 'time_range':
                    if ($schedule->isActiveForTimeRange()) {
                        return $schedule->media;
                    }
                    break;

                case 'countdown':
                    if ($this->isCountdownTime($now, $schedule, $prayerTimes)) {
                        return $schedule->media;
                    }
                    break;
            }
        }

        return null;
    }

    /**
     * Check if current time is before prayer time
     */
    private function isBeforePrayerTime(Carbon $now, MediaSchedule $schedule, PrayerTime $prayerTimes): bool
    {
        if (!$schedule->prayer_name) {
            return false;
        }

        $prayerTime = $this->getPrayerTime($prayerTimes, $schedule->prayer_name);
        if (!$prayerTime) {
            return false;
        }

        // Check if we're within the display duration before prayer time
        $displayStart = $prayerTime->subSeconds($schedule->media->display_duration);
        
        return $now->between($displayStart, $prayerTime);
    }

    /**
     * Check if current time is after prayer time
     */
    private function isAfterPrayerTime(Carbon $now, MediaSchedule $schedule, PrayerTime $prayerTimes): bool
    {
        if (!$schedule->prayer_name) {
            return false;
        }

        $prayerTime = $this->getPrayerTime($prayerTimes, $schedule->prayer_name);
        if (!$prayerTime) {
            return false;
        }

        // Check if we're within the display duration after prayer time
        $displayEnd = $prayerTime->addSeconds($schedule->media->display_duration);
        
        return $now->between($prayerTime, $displayEnd);
    }

    /**
     * Check if current time is countdown time before adhan
     */
    private function isCountdownTime(Carbon $now, MediaSchedule $schedule, PrayerTime $prayerTimes): bool
    {
        if (!$schedule->prayer_name) {
            return false;
        }

        $prayerTime = $this->getPrayerTime($prayerTimes, $schedule->prayer_name);
        if (!$prayerTime) {
            return false;
        }

        // Check if we're within countdown duration before prayer time
        $countdownStart = $prayerTime->subSeconds($schedule->countdown_duration);
        
        return $now->between($countdownStart, $prayerTime);
    }

    /**
     * Get prayer time Carbon instance
     */
    private function getPrayerTime(PrayerTime $prayerTimes, string $prayerName): ?Carbon
    {
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
     * Get default media (always display)
     */
    private function getDefaultMedia(): ?Media
    {
        return Media::where('is_active', true)
            ->where('priority', 0) // Default priority
            ->whereDoesntHave('schedules') // No specific schedules
            ->orderBy('created_at', 'asc')
            ->first();
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
