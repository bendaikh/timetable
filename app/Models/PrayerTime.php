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
        $timezone = (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
        $today = Carbon::now($timezone)->toDateString();

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
        $timezone = (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
        $tomorrow = Carbon::now($timezone)->addDay()->toDateString();

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

    public static function getNextPrayer()
    {
        $today = self::getTodayPrayerTimes();
        if (!$today) return null;

        $timezone = (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
        $now = Carbon::now($timezone);
        $prayers = [
            'fajr' => $today->fajr,
            'zohar' => $today->zohar,
            'asr' => $today->asr,
            'maghrib' => $today->maghrib,
            'isha' => $today->isha,
        ];

        foreach ($prayers as $name => $time) {
            // Parse the time string and set it to today's date
            $prayerTime = Carbon::createFromFormat('H:i:s', $time)->setDate($now->year, $now->month, $now->day);
            if ($prayerTime->gt($now)) {
                return [
                    'name' => $name,
                    'time' => $time,
                    'time_until' => $now->diffInSeconds($prayerTime)
                ];
            }
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
