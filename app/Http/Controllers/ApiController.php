<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerTime;
use App\Models\Announcement;
use App\Models\Hadeeth;
use App\Models\Setting;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function prayerTimes()
    {
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        return response()->json($prayerTimes);
    }

    public function announcements()
    {
        $announcements = Announcement::getActiveAnnouncements();
        return response()->json($announcements);
    }

    public function hadeeth()
    {
        $hadeeth = Hadeeth::getTodayHadeeth();
        return response()->json($hadeeth);
    }

    public function nextPrayer()
    {
        $nextPrayer = PrayerTime::getNextPrayer();
        return response()->json($nextPrayer);
    }

    public function settings()
    {
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
        ];
        
        return response()->json($settings);
    }
}
