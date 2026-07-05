<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'type',
        'description',
        'is_active',
        'display_duration'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Convert seconds to minutes for display
    public function getDisplayDurationAttribute($value)
    {
        return $value ? round($value / 60, 2) : 0;
    }

    // Convert minutes to seconds for storage
    public function setDisplayDurationAttribute($value)
    {
        // Convert minutes to seconds and store as integer
        // 0.5 minutes = 30 seconds
        $this->attributes['display_duration'] = $value ? intval(round($value * 60)) : 0;
    }

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(MediaSchedule::class, 'media_schedule_media')
            ->withPivot('duration', 'priority', 'is_active', 'start_date', 'start_time', 'expiry_date', 'expiry_time', 'gap_duration', 'days_of_week')
            ->orderBy('media_schedule_media.priority', 'asc')
            ->withTimestamps();
    }

    public function getFileUrlAttribute(): string
    {
        if (app()->environment('production')) {
            return url('public/storage/' . $this->file_path);
        }

        // For development, construct URL with proper port if needed
        $scheme = request()->isSecure() ? 'https' : 'http';
        $host = request()->getHost();
        $port = request()->getPort();

        // Only include port if it's not the default for the scheme
        if (($scheme === 'http' && $port != 80) || ($scheme === 'https' && $port != 443)) {
            $host .= ':' . $port;
        }

        return $scheme . '://' . $host . '/storage/' . $this->file_path;
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }
}
