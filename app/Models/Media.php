<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Media extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'type',
        'description',
        'is_active',
        'display_duration',
        'priority'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_duration' => 'integer',
        'priority' => 'integer'
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(MediaSchedule::class);
    }

    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
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
