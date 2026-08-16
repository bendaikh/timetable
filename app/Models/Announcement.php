<?php

namespace App\Models;

use App\Support\PrayerJamaatTime;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_active',
        'priority',
        'auto_repeat',
        'repeat_days',
        'start_date',
        'end_date',
        'display_duration',
        'font_size',
        'title_font_size',
        'text_color',
        'background_color',
        'scroll_speed',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_repeat' => 'boolean',
        'repeat_days' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'display_duration' => 'integer',
        'font_size' => 'float',
        'title_font_size' => 'float',
        'scroll_speed' => 'integer',
        'priority' => 'integer',
        'display_order' => 'integer',
    ];

    public const WEEKDAYS = [
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
    ];

    /**
     * Format a stored rem font size for admin labels (e.g. "2.25rem").
     */
    public function formattedFontSize(?float $fallback = null): string
    {
        return \App\Support\CssUnits::normalizeAnnouncementRem($this->font_size, ($fallback ?? 1.5) . 'rem');
    }

    public function formattedTitleFontSize(?float $fallback = null): string
    {
        return \App\Support\CssUnits::normalizeAnnouncementRem($this->title_font_size, ($fallback ?? 2.25) . 'rem');
    }

    /**
     * Safe HTML body with preserved line breaks for the display screen.
     */
    public function contentHtml(): string
    {
        $normalized = str_replace(["\r\n", "\r"], "\n", (string) $this->content);

        return nl2br(e($normalized), false);
    }

    public function scheduleStartDateValue(): string
    {
        return $this->start_date ? $this->start_date->format('Y-m-d') : '';
    }

    public function scheduleStartTimeValue(): string
    {
        return $this->start_date ? $this->start_date->format('H:i') : '';
    }

    public function scheduleEndDateValue(): string
    {
        return $this->end_date ? $this->end_date->format('Y-m-d') : '';
    }

    public function scheduleEndTimeValue(): string
    {
        return $this->end_date ? $this->end_date->format('H:i') : '';
    }

    public function formattedScheduleWindow(): string
    {
        if (!$this->start_date && !$this->end_date) {
            return 'Always (no schedule)';
        }

        $start = $this->start_date ? $this->start_date->format('M j, Y g:i A') : 'Anytime';
        $end = $this->end_date ? $this->end_date->format('M j, Y g:i A') : 'No end';

        return "{$start} → {$end}";
    }

    /**
     * Build a datetime from separate admin date + time inputs.
     * Values are interpreted in the mosque timezone (same clock as the display).
     * End times are inclusive through that minute (seconds = 59).
     */
    public static function combineScheduleDateTime(?string $date, ?string $time, bool $isEnd = false): ?Carbon
    {
        $date = $date !== null ? trim($date) : '';
        $time = $time !== null ? trim($time) : '';

        if ($date === '') {
            return null;
        }

        if ($time === '') {
            $time = $isEnd ? '23:59' : '00:00';
        }

        $timezone = PrayerJamaatTime::appTimezone();
        $dateTime = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}", $timezone);

        return $isEnd
            ? $dateTime->copy()->second(59)
            : $dateTime->copy()->second(0);
    }

    public function isWithinSchedule(?Carbon $now = null): bool
    {
        $now = PrayerJamaatTime::now($now);

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        return true;
    }

    /**
     * Normalized lowercase weekday names from repeat_days JSON.
     *
     * @return list<string>
     */
    public function normalizedRepeatDays(): array
    {
        if (!is_array($this->repeat_days)) {
            return [];
        }

        $days = [];
        foreach ($this->repeat_days as $day) {
            if (!is_string($day)) {
                continue;
            }

            $normalized = strtolower(trim($day));
            if (in_array($normalized, self::WEEKDAYS, true)) {
                $days[] = $normalized;
            }
        }

        return array_values(array_unique($days));
    }

    /**
     * Whether this announcement is allowed on the given mosque-local weekday.
     * Unrestricted when auto_repeat is off and no days are stored.
     * Otherwise only the configured weekdays apply (mosque timezone).
     */
    public function matchesRepeatDays(?Carbon $now = null): bool
    {
        $now = PrayerJamaatTime::now($now);
        $today = strtolower($now->format('l'));
        $days = $this->normalizedRepeatDays();

        if (!$this->auto_repeat && $days === []) {
            return true;
        }

        return $days !== [] && in_array($today, $days, true);
    }

    // Accessor for 'active' field (defaults to is_active if not set)
    public function getActiveAttribute($value)
    {
        return $value !== null ? $value : $this->is_active;
    }

    public static function getActiveAnnouncements()
    {
        $now = PrayerJamaatTime::now();

        return self::where('is_active', true)
            ->where(function ($query) use ($now) {
                // Visible only when current mosque datetime is within [start, end]
                $query->where(function ($dateQuery) use ($now) {
                    $dateQuery->whereNull('start_date')
                              ->orWhere('start_date', '<=', $now);
                })
                ->where(function ($dateQuery) use ($now) {
                    $dateQuery->whereNull('end_date')
                              ->orWhere('end_date', '>=', $now);
                });
            })
            ->orderBy('display_order')
            ->orderBy('id')
            ->get()
            ->filter(fn (self $announcement) => $announcement->matchesRepeatDays($now))
            ->values();
    }

    /**
     * Next display_order value for a new announcement (1, 2, 3…).
     */
    public static function nextDisplayOrder(): int
    {
        return ((int) self::max('display_order')) + 1;
    }

    public function isActiveToday(?Carbon $now = null)
    {
        $now = PrayerJamaatTime::now($now);

        if (!$this->is_active) {
            return false;
        }

        if (!$this->isWithinSchedule($now)) {
            return false;
        }

        return $this->matchesRepeatDays($now);
    }

    public function formattedDisplayDuration(): string
    {
        $seconds = max(1, (int) $this->display_duration);

        if ($seconds < 60) {
            return "{$seconds}s";
        }

        $minutes = intdiv($seconds, 60);
        $remainder = $seconds % 60;

        return $remainder > 0 ? "{$minutes}m {$remainder}s" : "{$minutes}m";
    }
}
