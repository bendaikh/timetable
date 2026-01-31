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
        
        // Get tomorrow's prayer times
        $tomorrowPrayerTimes = PrayerTime::getTomorrowPrayerTimes();
        
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
        
        return compact('prayerTimes', 'tomorrowPrayerTimes', 'nextPrayer', 'announcements', 'hadeeth', 'hadeeths', 'slidingTexts', 'settings', 'boxSettings', 'islamicDate', 'now', 'useBoxesStyling', 'activeBoxTypes', 'prayerContent', 'specialTimesContent', 'hadeethContent', 'announcementsContent');
    }
    
    private function getIslamicDate($date)
    {
        // Islamic calendar calculation for Saudi Arabia
        // Using Umm al-Qura calendar (Saudi Arabia's official calendar)
        
        // Convert to Saudi Arabia timezone for accurate Islamic date
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
        
        // Manual Julian Day Number Calculation (since toJulianDay() is not available in all Carbon versions)
        $year = (int)$saudiDate->format('Y');
        $month = (int)$saudiDate->format('m');
        $day = (int)$saudiDate->format('d');
        
        // Julian Day Number formula
        $a = intdiv((14 - $month), 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        
        $jd = $day + intdiv((153 * $m + 2), 5) + 365 * $y + intdiv($y, 4) - intdiv($y, 100) + intdiv($y, 400) - 32045;
        
        // Umm Al-Qura Calendar Conversion
        // Reference: 1 Muharram 1 AH = July 16, 622 CE (JD = 1948440)
        $islamicEpochJD = 1948440;
        
        $daysSinceEpoch = $jd - $islamicEpochJD;
        
        // Calculate Islamic year (average Islamic year = 354.36667 days)
        $islamicYear = intdiv($daysSinceEpoch, 354) + 1;
        
        // Days in each Islamic month: alternates 30 and 29
        $monthDays = [30, 29, 30, 29, 30, 29, 30, 29, 30, 29, 30, 29];
        
        // Calculate position within Islamic year
        $dayInYear = $daysSinceEpoch % 354;
        if ($dayInYear < 0) {
            $dayInYear += 354;
            $islamicYear -= 1;
        }
        
        // Calculate Islamic month and day
        $islamicMonth = 1;
        $islamicDay = $dayInYear + 1;
        
        // Adjust for leap years in Umm al-Qura calendar
        // Years 2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29 (mod 30) have 30 days in month 12
        $yearInCycle = $islamicYear % 30;
        $leapYears = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];
        
        if (in_array($yearInCycle, $leapYears) || $yearInCycle === 0) {
            $monthDays[11] = 30; // Month 12 has 30 days in leap years
        }
        
        // Determine which month and day
        foreach ($monthDays as $index => $daysInMonth) {
            if ($islamicDay <= $daysInMonth) {
                $islamicMonth = $index + 1;
                break;
            }
            $islamicDay -= $daysInMonth;
        }
        
        // Ensure valid values
        if ($islamicMonth > 12) {
            $islamicMonth = 12;
        }
        if ($islamicDay > 30) {
            $islamicDay = 30;
        }
        if ($islamicDay < 1) {
            $islamicDay = 1;
        }
        
        // Return the calculated Islamic date
        return [
            'day' => (string)(int)$islamicDay,
            'month' => $islamicMonths[$islamicMonth] ?? 'Unknown',
            'year' => (string)$islamicYear
        ];
    }
}
