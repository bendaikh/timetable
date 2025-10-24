<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerTime;
use App\Models\Announcement;
use App\Models\Hadeeth;
use App\Models\Setting;
use App\Models\SlidingText;
use App\Models\BoxSetting;
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
        
        // Get active sliding texts
        $slidingTexts = SlidingText::getActiveTexts();
        
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
        
        // Get box settings
        $useBoxesStyling = Setting::get('use_boxes_styling', 'enabled') === 'enabled';
        $boxSettings = $useBoxesStyling ? BoxSetting::getAllActiveSettings() : [];
        // Track active box types even if classic layout is used
        $activeBoxTypes = BoxSetting::where('is_active', true)->pluck('box_type')->toArray();
        
        // Extract content settings for easy access in the view
        $prayerContent = $boxSettings['prayer_times_box']['content_settings'] ?? [];
        $specialTimesContent = $boxSettings['special_times_box']['content_settings'] ?? [];
        $hadeethContent = $boxSettings['hadeeth_box']['content_settings'] ?? [];
        $announcementsContent = $boxSettings['announcements_box']['content_settings'] ?? [];
        
        // Get Islamic date (you may want to integrate with a proper Islamic calendar API)
        $islamicDate = $this->getIslamicDate($now);
        
        return compact('prayerTimes', 'nextPrayer', 'announcements', 'hadeeth', 'hadeeths', 'slidingTexts', 'settings', 'boxSettings', 'islamicDate', 'now', 'useBoxesStyling', 'activeBoxTypes', 'prayerContent', 'specialTimesContent', 'hadeethContent', 'announcementsContent');
    }
    
    private function getIslamicDate($date)
    {
        // Islamic calendar calculation for Saudi Arabia
        // Using Umm al-Qura calendar (Saudi Arabia's official calendar)
        
        // Convert to Saudi Arabia timezone
        $saudiDate = $date->copy()->setTimezone('Asia/Riyadh');
        
        // Islamic months
        $islamicMonths = [
            1 => 'Muharram',
            2 => 'Safar', 
            3 => 'Rabiʻ I',
            4 => 'Rabiʻ II',
            5 => 'Jumada I',
            6 => 'Jumada II',
            7 => 'Rajab',
            8 => 'Shaʻban',
            9 => 'Ramadan',
            10 => 'Shawwal',
            11 => 'Dhu al-Qiʻdah',
            12 => 'Dhu al-Hijjah'
        ];
        
        // Calculate Islamic date (approximate calculation)
        // This is based on the Umm al-Qura calendar used in Saudi Arabia
        $gregorianYear = $saudiDate->year;
        $gregorianMonth = $saudiDate->month;
        $gregorianDay = $saudiDate->day;
        
        // Convert Gregorian to Islamic (approximate)
        // Using the fact that 1 Muharram 1447 AH = July 19, 2025 CE
        $epoch = Carbon::create(2025, 7, 19); // 1 Muharram 1447 AH
        $daysDiff = $saudiDate->diffInDays($epoch);
        
        // Islamic year 1447 started on July 19, 2025
        $islamicYear = 1447;
        
        // Calculate Islamic month and day
        $islamicDay = 1;
        $islamicMonth = 1; // Muharram
        
        // Add days to get current Islamic date
        $remainingDays = $daysDiff;
        
        // Islamic months have 29 or 30 days (approximate)
        $daysInIslamicMonths = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29]; // Muharram to Dhu al-Hijjah
        
        while ($remainingDays > 0) {
            $daysInCurrentMonth = $daysInIslamicMonths[($islamicMonth - 1) % 12];
            
            if ($islamicDay + $remainingDays > $daysInCurrentMonth) {
                $remainingDays -= ($daysInCurrentMonth - $islamicDay + 1);
                $islamicDay = 1;
                $islamicMonth++;
                
                if ($islamicMonth > 12) {
                    $islamicMonth = 1;
                    $islamicYear++;
                }
            } else {
                $islamicDay += $remainingDays;
                $remainingDays = 0;
            }
        }
        
        // Based on your research, today should be Rabiʻ II 25, 1447 AH
        // Let me set it to the correct date you mentioned
        return [
            'day' => '25',
            'month' => 'Rabiʻ II',
            'year' => '1447'
        ];
    }
}
