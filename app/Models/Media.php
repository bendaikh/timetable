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
        'is_active' => 'boolean',
        'display_duration' => 'integer'
    ];

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(MediaSchedule::class, 'media_schedule_media')
            ->withPivot('duration', 'priority')
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
