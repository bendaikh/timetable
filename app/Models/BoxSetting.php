<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoxSetting extends Model
{
    protected $fillable = [
        'box_type',
        'box_name',
        'content_settings',
        'styling_settings',
        'layout_settings',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'content_settings' => 'array',
        'styling_settings' => 'array',
        'layout_settings' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Get default box settings for all box types
     * These match the classic theme's appearance
     */
    public static function getDefaultBoxSettings()
    {
        return [
            'header_box' => [
                'box_name' => 'Header Box',
                'content_settings' => [],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.95)',
                    'text_color' => '#000000',
                    'time_font_size' => '3rem',
                    'date_font_size' => '1.6rem',
                    'font_family' => 'Courier New, monospace',
                    'border_color' => '#000000',
                    'border_width' => '3px',
                    'padding' => '25px'
                ],
                'layout_settings' => [
                    'position' => 'top',
                    'height' => 'auto'
                ]
            ],
            'sliding_text_box' => [
                'box_name' => 'Sliding Text',
                'content_settings' => [],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'padding' => '10px'
                ],
                'layout_settings' => [
                    'text_alignment' => 'left'
                ]
            ],
            'prayer_times_box' => [
                'box_name' => 'Prayer Times Table',
                'content_settings' => [
                    'beginning_title' => 'Beginning',
                    'jamaat_time_title' => 'Jamaat Time'
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'header_background_color' => 'transparent',
                    'header_text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '2rem',
                    'header_font_size' => '1.2rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'padding' => '20px',
                    'next_prayer_font_size' => '1.4rem',
                    'next_prayer_text_color' => '#000000',
                    'next_prayer_countdown_font_size' => '1.4rem',
                    'next_prayer_countdown_color' => '#000000',
                    'next_prayer_name_font_size' => '0.9rem',
                    'next_prayer_name_color' => '#666666'
                ],
                'layout_settings' => [
                    'position' => 'left_column',
                    'column_widths' => ['45%', '25%', '25%'],
                    'next_prayer_position' => 'below_table',
                    'beginning_column_spacing' => '0'
                ]
            ],
            'special_times_box' => [
                'box_name' => 'Special Times',
                'content_settings' => [
                    'sehri_ends_title' => 'Sehri Ends',
                    'sun_rise_title' => 'Sun Rise',
                    'noon_title' => 'Noon',
                    'jumah_1_title' => 'Jumu\'ah 1',
                    'jumah_2_title' => 'Jumu\'ah 2',
                    'eid_prayer_1_title' => 'Eid Prayer 1',
                    'eid_prayer_2_title' => 'Eid Prayer 2'
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'header_text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '1.2rem',
                    'header_font_size' => '1rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'padding' => '15px'
                ],
                'layout_settings' => [
                    'position' => 'bottom',
                    'column_widths' => ['14%', '14%', '14%', '14%', '14%', '15%', '15%']
                ]
            ],
            'hadeeth_box' => [
                'box_name' => 'Hadeeth of The Day',
                'content_settings' => [
                    'title' => 'Hadeeth Of The Day'
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'title_color' => '#000000',
                    'font_size' => '1.2rem',
                    'title_font_size' => '1.6rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'padding' => '20px'
                ],
                'layout_settings' => [
                    'position' => 'middle_column'
                ]
            ],
            'announcements_box' => [
                'box_name' => 'Announcements',
                'content_settings' => [
                    'title' => 'Announcements'
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'title_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '1.1rem',
                    'title_font_size' => '1.4rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'padding' => '20px'
                ],
                'layout_settings' => [
                    'position' => 'right_column',
                    'height' => 'auto'
                ]
            ],
            'note_prayer_box' => [
                'box_name' => 'Next Prayer Countdown',
                'content_settings' => [
                    'text' => 'Next prayer in:',
                    'show_countdown' => true
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '1.2rem',
                    'border_color' => '#000000',
                    'border_width' => '1px',
                    'border_radius' => '0px',
                    'padding' => '10px'
                ],
                'layout_settings' => [
                    'position' => 'below_prayer_times',
                    'text_alignment' => 'center'
                ]
            ],
            'timetable_background_box' => [
                'box_name' => 'Timetable Background',
                'content_settings' => [],
                'styling_settings' => [
                    'background_color' => '#fdf7e6'
                ],
                'layout_settings' => []
            ]
        ];
    }

    /**
     * Initialize default box settings
     */
    public static function initializeDefaults()
    {
        $defaults = self::getDefaultBoxSettings();
        
        // Delete all existing box configurations to start fresh
        self::whereIn('box_type', array_keys($defaults))->delete();
        
        // Create fresh default configurations
        foreach ($defaults as $boxType => $settings) {
            self::create([
                'box_type' => $boxType,
                'box_name' => $settings['box_name'],
                'content_settings' => $settings['content_settings'],
                'styling_settings' => $settings['styling_settings'],
                'layout_settings' => $settings['layout_settings'],
                'is_active' => true,
                'sort_order' => array_search($boxType, array_keys($defaults))
            ]);
        }
    }

    /**
     * Get settings for a specific box type
     */
    public static function getBoxSettings($boxType)
    {
        $box = self::where('box_type', $boxType)->where('is_active', true)->first();
        
        if (!$box) {
            $defaults = self::getDefaultBoxSettings();
            return $defaults[$boxType] ?? null;
        }
        
        return [
            'box_name' => $box->box_name,
            'content_settings' => $box->content_settings ?? [],
            'styling_settings' => $box->styling_settings ?? [],
            'layout_settings' => $box->layout_settings ?? []
        ];
    }

    /**
     * Get all active box settings
     */
    public static function getAllActiveSettings()
    {
        $boxes = self::where('is_active', true)->orderBy('sort_order')->get();
        $settings = [];
        
        foreach ($boxes as $box) {
            $settings[$box->box_type] = [
                'box_name' => $box->box_name,
                'content_settings' => $box->content_settings ?? [],
                'styling_settings' => $box->styling_settings ?? [],
                'layout_settings' => $box->layout_settings ?? []
            ];
        }
        
        return $settings;
    }

    /**
     * Convert RGBA color to hex for color picker input
     */
    public static function rgbaToHex($rgba)
    {
        if (strpos($rgba, 'rgba') !== 0) {
            return $rgba; // Already hex or other format
        }
        
        // Extract RGBA values: rgba(253, 247, 230, 0.9)
        preg_match('/rgba\((\d+),\s*(\d+),\s*(\d+),\s*[\d.]+\)/', $rgba, $matches);
        
        if (count($matches) === 4) {
            $r = intval($matches[1]);
            $g = intval($matches[2]);
            $b = intval($matches[3]);
            
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
        
        return '#fdf7e6'; // Default fallback
    }

    /**
     * Get background color in hex format for color picker
     */
    public function getBackgroundColorHex()
    {
        $bgColor = $this->styling_settings['background_color'] ?? null;
        
        // If no color is set, return default based on box type
        if (empty($bgColor)) {
            if ($this->box_type === 'timetable_background_box') {
                return '#fdf7e6';
            }
            return '#ffffff';
        }
        
        return self::rgbaToHex($bgColor);
    }
}
