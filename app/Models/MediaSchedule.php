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
        'days_of_week',
        'start_time',
        'end_time',
        'countdown_duration',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'countdown_duration' => 'integer',
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

    public function isActiveForTimeRange(): bool
    {
        if (!$this->is_active || !$this->start_time || !$this->end_time) {
            return false;
        }

        $now = Carbon::now();
        $startTime = Carbon::today()->setTimeFromTimeString($this->start_time->format('H:i:s'));
        $endTime = Carbon::today()->setTimeFromTimeString($this->end_time->format('H:i:s'));

        // Handle time ranges that cross midnight
        if ($startTime->greaterThan($endTime)) {
            return $now->greaterThanOrEqualTo($startTime) || $now->lessThanOrEqualTo($endTime);
        }

        return $now->between($startTime, $endTime);
    }

    public function getScheduleTypeLabel(): string
    {
        return match($this->schedule_type) {
            'prayer_before' => 'Before Prayer',
            'prayer_after' => 'After Prayer',
            'time_range' => 'Time Range',
            'countdown' => 'Countdown Timer',
            default => 'Unknown'
        };
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
