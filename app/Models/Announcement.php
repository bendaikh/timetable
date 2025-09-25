<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'is_active',
        'auto_repeat',
        'repeat_days',
        'start_date',
        'end_date',
        'display_duration',
        'font_size',
        'text_color',
        'background_color',
        'scroll_speed'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_repeat' => 'boolean',
        'repeat_days' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'display_duration' => 'integer',
        'font_size' => 'integer',
        'scroll_speed' => 'integer'
    ];

    public static function getActiveAnnouncements()
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l')); // e.g., 'monday'

        return self::where('is_active', true)
            ->where(function ($query) use ($now, $today) {
                $query->where(function ($q) use ($now) {
                    // Check date range
                    $q->where(function ($dateQuery) use ($now) {
                        $dateQuery->whereNull('start_date')
                                  ->orWhere('start_date', '<=', $now);
                    })
                    ->where(function ($dateQuery) use ($now) {
                        $dateQuery->whereNull('end_date')
                                  ->orWhere('end_date', '>=', $now);
                    });
                })
                ->where(function ($q) use ($today) {
                    // Check repeat days
                    $q->where('auto_repeat', false)
                      ->orWhereJsonContains('repeat_days', $today);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function isActiveToday()
    {
        $now = Carbon::now();
        $today = strtolower($now->format('l'));

        if (!$this->is_active) {
            return false;
        }

        // Check date range
        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        // Check repeat days
        if ($this->auto_repeat && $this->repeat_days) {
            return in_array($today, $this->repeat_days);
        }

        return true;
    }
}
