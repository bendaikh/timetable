<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PrayerTime extends Model
{
    protected $fillable = [
        'date',
        'fajr',
        'zohar',
        'asr',
        'maghrib',
        'isha',
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
        return self::whereDate('date', Carbon::today())->first();
    }

    public static function getNextPrayer()
    {
        $today = self::getTodayPrayerTimes();
        if (!$today) return null;

        $now = Carbon::now();
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
}
