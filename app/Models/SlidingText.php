<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlidingText extends Model
{
    protected $fillable = [
        'text',
        'is_active',
        'animation_speed',
        'font_size',
        'font_weight',
        'text_color',
        'background_color',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'animation_speed' => 'integer',
        'font_size' => 'integer',
        'display_order' => 'integer'
    ];

    /**
     * Get active sliding texts ordered by display order
     */
    public static function getActiveTexts()
    {
        return self::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Scope to get only active texts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
