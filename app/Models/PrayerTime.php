<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Setting;
use Carbon\Carbon;

class PrayerTime extends Model
{
    protected $fillable = [
        'date',
        'fajr',
        'fajr_adhan',
        'fajr_jamaat',
        'zohar',
        'zohar_adhan',
        'zohar_jamaat',
        'asr',
        'asr_adhan',
        'asr_jamaat',
        'maghrib',
        'maghrib_adhan',
        'maghrib_jamaat',
        'isha',
        'isha_adhan',
        'isha_jamaat',
        'sun_rise',
        'jumah_1',
        'jumah_2',
        'eid_prayer_1',
        'eid_prayer_2'
    ];

    protected $casts = [
        'date' => 'date',
    ];
    
    // Accessor methods to format time fields
    public function getFajrAttribute($value)
    {
        return $value;
    }
    
    public function getZoharAttribute($value)
    {
        return $value;
    }
    
    public function getAsrAttribute($value)
    {
        return $value;
    }
    
    public function getMaghribAttribute($value)
    {
        return $value;
    }
    
    public function getIshaAttribute($value)
    {
        return $value;
    }
    
    public function getSunRiseAttribute($value)
    {
        return $value;
    }
    
    public function getJumah1Attribute($value)
    {
        return $value;
    }
    
    public function getJumah2Attribute($value)
    {
        return $value;
    }
    
    public function getEidPrayer1Attribute($value)
    {
        return $value;
    }
    
    public function getEidPrayer2Attribute($value)
    {
        return $value;
    }

    public static function getTodayPrayerTimes()
    {
        $today = \App\Support\PrayerJamaatTime::now()->toDateString();

        $record = self::whereDate('date', $today)->first();
        if ($record) {
            return $record;
        }

        if (!self::isDateWithinUploadedTimetableRange($today)) {
            return null;
        }

        return null;
    }

    public static function getTomorrowPrayerTimes()
    {
        $tomorrow = \App\Support\PrayerJamaatTime::now()->addDay()->toDateString();

        $record = self::whereDate('date', $tomorrow)->first();
        if ($record) {
            return $record;
        }

        if (!self::isDateWithinUploadedTimetableRange($tomorrow)) {
            return null;
        }

        return null;
    }

    public static function refreshUploadedTimetableRange(): void
    {
        $min = self::min('date');
        $max = self::max('date');

        if ($min) {
            Setting::set(
                'timetable_min_date',
                Carbon::parse($min)->toDateString(),
                'string',
                'Earliest date available in uploaded timetable'
            );
        }

        if ($max) {
            Setting::set(
                'timetable_max_date',
                Carbon::parse($max)->toDateString(),
                'string',
                'Latest date available in uploaded timetable'
            );
        }
    }

    /**
     * Next prayer for the product = next JAMAAT (iqamah), not Beginning/Adhan.
     *
     * Matches the TV "Next prayer in" countdown which uses PrayerJamaatTime::resolve
     * (explicit {prayer}_jamaat column, else beginning + settings offset).
     *
     * Consumers: /api/next-prayer, TimetableController, admin dashboard.
     */
    public static function getNextPrayer()
    {
        $today = self::getTodayPrayerTimes();
        if (!$today) {
            return null;
        }

        $now = \App\Support\PrayerJamaatTime::now();
        $prayers = ['fajr', 'zohar', 'asr', 'maghrib', 'isha'];

        foreach ($prayers as $name) {
            $jamaat = \App\Support\PrayerJamaatTime::resolve($today, $name, $now);
            if ($jamaat && $jamaat->gt($now)) {
                return [
                    'name' => $name,
                    'time' => $jamaat->format('H:i:s'),
                    'time_until' => $now->diffInSeconds($jamaat),
                    'reference' => 'jamaat',
                ];
            }
        }

        // After Isha: next is tomorrow's Fajr jamaat (same as the TV display).
        $tomorrow = self::getTomorrowPrayerTimes();
        if ($tomorrow) {
            $fajr = \App\Support\PrayerJamaatTime::resolve($tomorrow, 'fajr', $now);
        } else {
            $fajr = \App\Support\PrayerJamaatTime::resolve($today, 'fajr', $now)?->addDay();
        }

        if ($fajr && $fajr->gt($now)) {
            return [
                'name' => 'fajr',
                'time' => $fajr->format('H:i:s'),
                'time_until' => $now->diffInSeconds($fajr),
                'reference' => 'jamaat',
            ];
        }

        return null;
    }

    private static function isDateWithinUploadedTimetableRange(string $date): bool
    {
        $min = Setting::get('timetable_min_date');
        $max = Setting::get('timetable_max_date');

        if ($min && is_string($min) && $date < $min) {
            return false;
        }

        if ($max && is_string($max) && $date > $max) {
            return false;
        }

        return true;
    }
}
