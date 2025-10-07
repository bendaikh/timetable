<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MediaSchedule extends Model
{
    protected $fillable = [
        'media_id',
        'schedule_type',
        'prayer_name',
        'minutes_before_prayer',
        'minutes_after_prayer',
        'days_of_week',
        'start_time',
        'end_time',
        'relative_duration',
        'exact_start_time',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'minutes_before_prayer' => 'integer',
        'minutes_after_prayer' => 'integer',
        'relative_duration' => 'integer',
        'exact_start_time' => 'datetime:H:i',
        'is_active' => 'boolean',
        'priority' => 'integer'
    ];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
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
     * Check if this schedule should be active based on minutes before prayer
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

        $prayerDateTime = Carbon::parse($prayerTime->$prayerTimeField);
        $displayStartTime = $prayerDateTime->copy()->subMinutes($this->minutes_before_prayer);
        $displayEndTime = $prayerDateTime->copy()->subMinutes(5); // Stop 5 minutes before prayer

        $now = Carbon::now();

        return $now->between($displayStartTime, $displayEndTime);
    }

    /**
     * Get the display start time for this schedule
     */
    public function getDisplayStartTime(): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer' && $this->prayer_name && $this->minutes_before_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    return Carbon::parse($prayerTime->$prayerTimeField)->subMinutes($this->minutes_before_prayer);
                }
            }
        }
        
        if ($this->schedule_type === 'minutes_after_prayer' && $this->prayer_name && $this->minutes_after_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    return Carbon::parse($prayerTime->$prayerTimeField)->addMinutes($this->minutes_after_prayer);
                }
            }
        }

        return null;
    }

    /**
     * Get the display end time for this schedule
     */
    public function getDisplayEndTime(): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer' && $this->prayer_name && $this->minutes_before_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    return Carbon::parse($prayerTime->$prayerTimeField)->subMinutes(5); // Stop 5 minutes before prayer
                }
            }
        }
        
        if ($this->schedule_type === 'minutes_after_prayer' && $this->prayer_name && $this->minutes_after_prayer) {
            $prayerTime = PrayerTime::getTodayPrayerTimes();
            if ($prayerTime) {
                $prayerTimeField = $this->prayer_name;
                if (isset($prayerTime->$prayerTimeField)) {
                    // End at start time + media duration window; as a simple rule, end at + (minutes_after + 10)
                    // The service will cycle media by its own display duration; here we just mark an end window.
                    return Carbon::parse($prayerTime->$prayerTimeField)->addMinutes($this->minutes_after_prayer + 10);
                }
            }
        }

        return null;
    }

    /**
     * Scope to get schedules ordered by priority
     */
    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
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
            default => 'Unknown'
        };
    }

    /**
     * Check if this schedule should be active based on minutes after prayer
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

        $prayerDateTime = Carbon::parse($prayerTime->$prayerTimeField);
        $displayStartTime = $prayerDateTime->copy()->addMinutes($this->minutes_after_prayer);
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
