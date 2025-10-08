<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Carbon\Carbon;
use App\Models\Setting;

class MediaSchedule extends Model
{
    protected $fillable = [
        'schedule_type',
        'prayer_name',
        'minutes_before_prayer',
        'minutes_after_prayer',
        'days_of_week',
        'start_time',
        'end_time',
        'relative_duration',
        'exact_start_time',
        'is_active'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'minutes_before_prayer' => 'integer',
        'minutes_after_prayer' => 'integer',
        'relative_duration' => 'integer',
        'exact_start_time' => 'datetime:H:i',
        'is_active' => 'boolean'
    ];

    public function mediaItems(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_schedule_media')
            ->withPivot('duration', 'priority')
            ->orderBy('media_schedule_media.priority', 'asc')
            ->withTimestamps();
    }
    
    // Keep for backward compatibility, but deprecated
    public function media(): BelongsToMany
    {
        return $this->mediaItems();
    }

    public function isActiveForToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$this->days_of_week) {
            return true; // Active every day if no specific days set
        }

        $today = Carbon::now()->dayOfWeekIso; // 1-7 (Monday-Sunday)
        return in_array($today, $this->days_of_week);
    }

    public function isActiveForPrayer(string $prayerName): bool
    {
        if (!$this->is_active || !$this->prayer_name) {
            return false;
        }

        return $this->prayer_name === $prayerName;
    }


    /**
     * Check if this schedule should be active based on minutes before prayer (uses JAMAAT TIME)
     */
    public function isActiveForMinutesBeforePrayer(): bool
    {
        if (!$this->is_active || !$this->prayer_name || !$this->minutes_before_prayer) {
            return false;
        }

        // Get today's prayer times
        $prayerTime = PrayerTime::getTodayPrayerTimes();
        
        if (!$prayerTime) {
            return false;
        }

        // Get the prayer time for the specified prayer
        $prayerTimeField = $this->prayer_name;
        if (!isset($prayerTime->$prayerTimeField)) {
            return false;
        }

        // Get beginning time and add jamaat offset to get actual Jamaat time
        $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
        $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
        $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
        
        $displayStartTime = $jamaatTime->copy()->subMinutes($this->minutes_before_prayer);
        $displayEndTime = $jamaatTime->copy()->subMinutes(5); // Stop 5 minutes before Jamaat

        $now = Carbon::now();

        return $now->between($displayStartTime, $displayEndTime);
    }

    /**
     * Get the display start time for this schedule (based on JAMAAT TIME)
     */
    public function getDisplayStartTime(): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer' && $this->prayer_name && $this->minutes_before_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    // Get beginning time and add jamaat offset to get actual Jamaat time
                    $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
                    $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
                    $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
                    
                    return $jamaatTime->subMinutes($this->minutes_before_prayer);
                }
            }
        }
        
        if ($this->schedule_type === 'minutes_after_prayer' && $this->prayer_name && $this->minutes_after_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    // Get beginning time and add jamaat offset to get actual Jamaat time
                    $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
                    $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
                    $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
                    
                    return $jamaatTime->addMinutes($this->minutes_after_prayer);
                }
            }
        }

        return null;
    }

    /**
     * Get the display end time for this schedule (based on JAMAAT TIME)
     */
    public function getDisplayEndTime(): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer' && $this->prayer_name && $this->minutes_before_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    // Get beginning time and add jamaat offset to get actual Jamaat time
                    $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
                    $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
                    $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
                    
                    return $jamaatTime->subMinutes(5); // Stop 5 minutes before Jamaat
                }
            }
        }
        
        if ($this->schedule_type === 'minutes_after_prayer' && $this->prayer_name && $this->minutes_after_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    // Get beginning time and add jamaat offset to get actual Jamaat time
                    $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
                    $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
                    $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
                    
                    // End at start time + media duration window; as a simple rule, end at + (minutes_after + 10)
                    // The service will cycle media by its own display duration; here we just mark an end window.
                    return $jamaatTime->addMinutes($this->minutes_after_prayer + 10);
                }
            }
        }

        return null;
    }

    /**
     * Scope to get schedules ordered by the earliest priority in their media items
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->leftJoin('media_schedule_media', 'media_schedules.id', '=', 'media_schedule_media.media_schedule_id')
            ->select('media_schedules.*')
            ->groupBy('media_schedules.id')
            ->orderByRaw('MIN(media_schedule_media.priority) ASC');
    }

    /**
     * Scope to get active schedules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getScheduleTypeLabel(): string
    {
        return match($this->schedule_type) {
            'minutes_before_prayer' => 'Minutes Before Prayer',
            'minutes_after_prayer' => 'Minutes After Prayer',
            'full_time_poster' => 'Full Time Poster',
            default => 'Unknown'
        };
    }

    /**
     * Check if this schedule should be active based on minutes after prayer (uses JAMAAT TIME)
     */
    public function isActiveForMinutesAfterPrayer(): bool
    {
        if (!$this->is_active || !$this->prayer_name || !$this->minutes_after_prayer) {
            return false;
        }

        $prayerTime = PrayerTime::getTodayPrayerTimes();
        if (!$prayerTime) {
            return false;
        }

        $prayerTimeField = $this->prayer_name;
        if (!isset($prayerTime->$prayerTimeField)) {
            return false;
        }

        // Get beginning time and add jamaat offset to get actual Jamaat time
        $beginningTime = Carbon::parse($prayerTime->$prayerTimeField);
        $jamaatOffset = (int) Setting::get($this->prayer_name . '_jamaat_offset', 0);
        $jamaatTime = $beginningTime->addMinutes($jamaatOffset);
        
        $displayStartTime = $jamaatTime->copy()->addMinutes($this->minutes_after_prayer);
        $displayEndTime = $this->getDisplayEndTime() ?? $displayStartTime->copy()->addMinutes(10);

        $now = Carbon::now();
        return $now->between($displayStartTime, $displayEndTime);
    }

    public function getPrayerNameLabel(): string
    {
        return match($this->prayer_name) {
            'fajr' => 'Fajr',
            'zohar' => 'Zohar',
            'asr' => 'Asr',
            'maghrib' => 'Maghrib',
            'isha' => 'Isha',
            default => 'All Prayers'
        };
    }
}
