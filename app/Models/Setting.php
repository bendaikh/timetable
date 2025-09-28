<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description'
    ];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return self::castValue($setting->value, $setting->type);
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'description' => $description
            ]
        );
    }

    private static function castValue($value, $type)
    {
        switch ($type) {
            case 'boolean':
                return (bool) $value;
            case 'integer':
                return (int) $value;
            case 'float':
                return (float) $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }

    public static function getDefaults()
    {
        return [
            'masjid_name' => 'Al Hidaya Academy',
            'location' => 'Your City, Your Country',
            'timezone' => 'UTC',
            'display_font_family' => 'Arial, sans-serif',
            'display_background_color' => '#ffffff',
            'display_text_color' => '#000000',
            'prayer_time_font_size' => '24',
            'announcement_scroll_speed' => '3',
            'hadeeth_display_duration' => '30',
            'auto_refresh_interval' => '60',
            'logo_path' => null,
            'fajr_jamaat_offset' => '10',
            'zohar_jamaat_offset' => '15',
            'asr_jamaat_offset' => '20',
            'maghrib_jamaat_offset' => '0',
            'isha_jamaat_offset' => '10',
            'adhan_timing_enabled' => true,
            'adhan_countdown_duration' => '30',
            'media_display_enabled' => true,
            'default_media_duration' => '30'
        ];
    }

    public static function initializeDefaults()
    {
        $defaults = self::getDefaults();
        
        foreach ($defaults as $key => $value) {
            if (!self::where('key', $key)->exists()) {
                self::set($key, $value);
            }
        }
    }
}
