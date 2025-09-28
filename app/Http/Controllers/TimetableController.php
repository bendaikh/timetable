<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerTime;
use App\Models\Announcement;
use App\Models\Hadeeth;
use App\Models\Setting;
use Carbon\Carbon;

class TimetableController extends Controller
{
    public function index()
    {
        $data = $this->getTimetableData();
        return view('timetable.index', $data);
    }


    private function getTimetableData()
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        
        // Get today's prayer times
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        
        // Get next prayer
        $nextPrayer = PrayerTime::getNextPrayer();
        
        // Get active announcements
        $announcements = Announcement::getActiveAnnouncements();
        
        // Get today's hadeeths (multiple for rotation)
        $hadeeth = Hadeeth::getTodayHadeeth();
        $hadeeths = Hadeeth::getOrderedHadeeths();
        
        // Get settings
        $settings = [
            'masjid_name' => Setting::get('masjid_name', 'Al Hidaya Academy'),
            'location' => Setting::get('location', 'Your City, Your Country'),
            'display_font_family' => Setting::get('display_font_family', 'Arial, sans-serif'),
            'display_background_color' => Setting::get('display_background_color', '#ffffff'),
            'display_text_color' => Setting::get('display_text_color', '#000000'),
            'prayer_time_font_size' => Setting::get('prayer_time_font_size', '24'),
            'announcement_scroll_speed' => Setting::get('announcement_scroll_speed', '3'),
            'hadeeth_display_duration' => Setting::get('hadeeth_display_duration', '30'),
            'auto_refresh_interval' => Setting::get('auto_refresh_interval', '60'),
            'logo_path' => Setting::get('logo_path'),
            'fajr_jamaat_offset' => Setting::get('fajr_jamaat_offset', '10'),
            'zohar_jamaat_offset' => Setting::get('zohar_jamaat_offset', '15'),
            'asr_jamaat_offset' => Setting::get('asr_jamaat_offset', '20'),
            'maghrib_jamaat_offset' => Setting::get('maghrib_jamaat_offset', '0'),
            'isha_jamaat_offset' => Setting::get('isha_jamaat_offset', '10'),
        ];
        
        // Get Islamic date (you may want to integrate with a proper Islamic calendar API)
        $islamicDate = $this->getIslamicDate($now);
        
        return compact('prayerTimes', 'nextPrayer', 'announcements', 'hadeeth', 'hadeeths', 'settings', 'islamicDate', 'now');
    }
    
    private function getIslamicDate($date)
    {
        // Simple Islamic date calculation (you may want to use a proper library)
        // This is a basic example - for production, use a proper Islamic calendar library
        return [
            'day' => '18',
            'month' => 'Safar',
            'year' => '1447'
        ];
    }
}
