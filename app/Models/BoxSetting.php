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
                'content_settings' => [
                    'time_format' => 'h:i',
                    'english_date_format' => 'D j M Y',
                    'islamic_date_format' => 'd F Y',
                    'timezone' => 'Europe/London',
                    'show_fullscreen_button' => true
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.95)',
                    'text_color' => '#000000',
                    'time_font_size' => '3rem',
                    'date_font_size' => '1.6rem',
                    'font_family' => 'Courier New, monospace',
                    'border_color' => '#000000',
                    'border_width' => '3px',
                    'border_radius' => '0px',
                    'padding' => '25px'
                ],
                'layout_settings' => [
                    'position' => 'top',
                    'height' => 'auto',
                    'text_alignment' => 'center'
                ]
            ],
            'sliding_text_box' => [
                'box_name' => 'Sliding Text',
                'content_settings' => [
                    'title' => 'Sliding Messages',
                    'scroll_speed' => 3
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '1.2rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'border_radius' => '0px',
                    'padding' => '10px'
                ],
                'layout_settings' => [
                    'text_alignment' => 'left'
                ]
            ],
            'prayer_times_box' => [
                'box_name' => 'Prayer Times Table',
                'content_settings' => [
                    'show_jamaat_times' => true,
                    'time_format' => 'h:i',
                    'table_headers' => ['', 'Beginning', 'Jamaat Time']
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
                    'border_radius' => '0px',
                    'padding' => '20px'
                ],
                'layout_settings' => [
                    'position' => 'left_column',
                    'column_widths' => ['45%', '25%', '25%'],
                    'text_alignment' => 'center'
                ]
            ],
            'special_times_box' => [
                'box_name' => 'Special Times',
                'content_settings' => [
                    'time_format' => 'h:i',
                    'show_sehri_ends' => true,
                    'show_sun_rise' => true,
                    'show_noon' => true,
                    'show_jumuah_1' => true,
                    'show_jumuah_2' => true,
                    'show_eid_prayer_1' => true,
                    'show_eid_prayer_2' => true,
                    'table_headers' => ['Sehri Ends', 'Sun Rise', 'Noon', 'Jumu\'ah 1', 'Jumu\'ah 2', 'Eid Prayer 1', 'Eid Prayer 2']
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'header_background_color' => 'transparent',
                    'header_text_color' => '#000000',
                    'font_family' => 'Courier New, monospace',
                    'font_size' => '1.2rem',
                    'header_font_size' => '1rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'border_radius' => '0px',
                    'padding' => '15px'
                ],
                'layout_settings' => [
                    'position' => 'bottom',
                    'column_widths' => ['14%', '14%', '14%', '14%', '14%', '15%', '15%'],
                    'text_alignment' => 'center'
                ]
            ],
            'hadeeth_box' => [
                'box_name' => 'Hadeeth of The Day',
                'content_settings' => [
                    'title' => 'Hadeeth Of The Day',
                    'show_arabic_text' => true,
                    'show_english_translation' => true,
                    'show_reference' => true,
                    'rotation_duration' => 30
                ],
                'styling_settings' => [
                    'background_color' => 'rgba(253, 247, 230, 0.9)',
                    'text_color' => '#000000',
                    'title_color' => '#000000',
                    'arabic_font_family' => 'Amiri, serif',
                    'english_font_family' => 'Courier New, monospace',
                    'font_size' => '1.2rem',
                    'title_font_size' => '1.6rem',
                    'border_color' => '#000000',
                    'border_width' => '2px',
                    'border_radius' => '0px',
                    'padding' => '20px',
                    'accent_color' => 'transparent'
                ],
                'layout_settings' => [
                    'position' => 'middle_column',
                    'text_alignment' => 'center'
                ]
            ],
            'announcements_box' => [
                'box_name' => 'Announcements',
                'content_settings' => [
                    'title' => 'Announcements',
                    'max_visible_announcements' => 2,
                    'character_limit' => 200,
                    'show_character_count' => true,
                    'rotation_duration' => 15
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
                    'border_radius' => '0px',
                    'padding' => '20px'
                ],
                'layout_settings' => [
                    'position' => 'right_column',
                    'text_alignment' => 'center',
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
        return self::rgbaToHex($bgColor);
    }
}
