<?php

namespace App\Models;

use App\Support\PrayerJamaatTime;
use App\Support\ScheduleDaysOfWeek;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
            ->withPivot('duration', 'priority', 'is_active', 'start_date', 'start_time', 'expiry_date', 'expiry_time', 'gap_duration', 'days_of_week')
            ->orderBy('media_schedule_media.priority', 'asc')
            ->withTimestamps();
    }
    
    // Keep for backward compatibility, but deprecated
    public function media(): BelongsToMany
    {
        return $this->mediaItems();
    }

    public function isActiveForToday(?Carbon $now = null): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return ScheduleDaysOfWeek::isActiveToday($this->days_of_week, PrayerJamaatTime::now($now));
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
    public function isActiveForMinutesBeforePrayer(?Carbon $now = null): bool
    {
        $window = $this->resolveBeforePrayerWindow($now);

        return $window
            ? PrayerJamaatTime::isWithinWindow(PrayerJamaatTime::now($now), $window['start'], $window['end'])
            : false;
    }

    /**
     * Get the display start time for this schedule (based on JAMAAT TIME)
     */
    public function getDisplayStartTime(?Carbon $now = null): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer') {
            return $this->resolveBeforePrayerWindow($now)['start'] ?? null;
        }

        if ($this->schedule_type === 'minutes_after_prayer') {
            return $this->resolveAfterPrayerWindow($now)['start'] ?? null;
        }

        return null;
    }

    /**
     * Get the display end time for this schedule (based on JAMAAT TIME)
     */
    public function getDisplayEndTime(?Carbon $now = null): ?Carbon
    {
        if ($this->schedule_type === 'minutes_before_prayer') {
            return $this->resolveBeforePrayerWindow($now)['end'] ?? null;
        }

        if ($this->schedule_type === 'minutes_after_prayer') {
            return $this->resolveAfterPrayerWindow($now)['end'] ?? null;
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
    public function isActiveForMinutesAfterPrayer(?Carbon $now = null): bool
    {
        $window = $this->resolveAfterPrayerWindow($now);

        return $window
            ? PrayerJamaatTime::isWithinWindow(PrayerJamaatTime::now($now), $window['start'], $window['end'])
            : false;
    }

    /**
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon, reference: string}|null
     */
    public function resolveBeforePrayerWindow(?Carbon $now = null): ?array
    {
        if (!$this->is_active || !$this->prayer_name || $this->minutes_before_prayer === null || (int) $this->minutes_before_prayer < 1) {
            return null;
        }

        $prayerTime = $this->prayerTimesFor($now);
        if (!$prayerTime) {
            return null;
        }

        return PrayerJamaatTime::beforePrayerPosterWindow($this, $prayerTime, $now);
    }

    /**
     * @return array{jamaat: Carbon, start: Carbon, end: Carbon, reference: string}|null
     */
    public function resolveAfterPrayerWindow(?Carbon $now = null): ?array
    {
        if (!$this->is_active || !$this->prayer_name || $this->minutes_after_prayer === null || (int) $this->minutes_after_prayer < 1) {
            return null;
        }

        $prayerTime = $this->prayerTimesFor($now);
        if (!$prayerTime) {
            return null;
        }

        return PrayerJamaatTime::afterPrayerPosterWindow($this, $prayerTime, $now);
    }

    private function prayerTimesFor(?Carbon $now = null): ?PrayerTime
    {
        $date = PrayerJamaatTime::now($now)->toDateString();

        return PrayerTime::whereDate('date', $date)->orderBy('id')->first()
            ?: PrayerTime::getTodayPrayerTimes();
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
