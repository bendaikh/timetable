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
        
        // Get box settings (full state: active/inactive + sort order)
        $useBoxesStyling = Setting::get('use_boxes_styling', 'enabled') === 'enabled';
        $boxSettings = $this->getCompleteBoxSettings();
        // Track active box types even if classic layout is used
        $activeBoxTypes = collect($boxSettings)
            ->filter(fn ($box) => (bool)($box['is_active'] ?? false))
            ->keys()
            ->values()
            ->toArray();
        
        // Extract content settings for easy access in the view
        $prayerContent = $boxSettings['prayer_times_box']['content_settings'] ?? [];
        $specialTimesContent = $boxSettings['special_times_box']['content_settings'] ?? [];
        $hadeethContent = $boxSettings['hadeeth_box']['content_settings'] ?? [];
        $announcementsContent = $boxSettings['announcements_box']['content_settings'] ?? [];
        
        // Get Islamic date (you may want to integrate with a proper Islamic calendar API)
        $islamicDate = $this->getIslamicDate($now);
        
        return compact('prayerTimes', 'tomorrowPrayerTimes', 'nextPrayer', 'announcements', 'hadeeth', 'hadeeths', 'slidingTexts', 'settings', 'boxSettings', 'islamicDate', 'now', 'useBoxesStyling', 'activeBoxTypes', 'prayerContent', 'specialTimesContent', 'hadeethContent', 'announcementsContent');
    }

    private function getCompleteBoxSettings(): array
    {
        $defaults = BoxSetting::getDefaultBoxSettings();
        $storedBoxes = BoxSetting::orderBy('sort_order')
            ->get()
            ->keyBy('box_type');
        $result = [];

        foreach ($defaults as $boxType => $default) {
            $stored = $storedBoxes->get($boxType);

            if ($stored) {
                $result[$boxType] = [
                    'box_name' => $stored->box_name ?: ($default['box_name'] ?? $boxType),
                    'is_active' => (bool) $stored->is_active,
                    'sort_order' => (int) $stored->sort_order,
                    'content_settings' => is_string($stored->content_settings) ? (json_decode($stored->content_settings, true) ?: []) : ($stored->content_settings ?? []),
                    'styling_settings' => is_string($stored->styling_settings) ? (json_decode($stored->styling_settings, true) ?: []) : ($stored->styling_settings ?? []),
                    'layout_settings' => is_string($stored->layout_settings) ? (json_decode($stored->layout_settings, true) ?: []) : ($stored->layout_settings ?? []),
                ];
                continue;
            }

            $result[$boxType] = [
                'box_name' => $default['box_name'] ?? $boxType,
                'is_active' => true,
                'sort_order' => count($result),
                'content_settings' => $default['content_settings'] ?? [],
                'styling_settings' => $default['styling_settings'] ?? [],
                'layout_settings' => $default['layout_settings'] ?? [],
            ];
        }

        foreach ($storedBoxes as $boxType => $stored) {
            if (isset($result[$boxType])) {
                continue;
            }

            $result[$boxType] = [
                'box_name' => $stored->box_name ?: $boxType,
                'is_active' => (bool) $stored->is_active,
                'sort_order' => (int) $stored->sort_order,
                'content_settings' => is_string($stored->content_settings) ? (json_decode($stored->content_settings, true) ?: []) : ($stored->content_settings ?? []),
                'styling_settings' => is_string($stored->styling_settings) ? (json_decode($stored->styling_settings, true) ?: []) : ($stored->styling_settings ?? []),
                'layout_settings' => is_string($stored->layout_settings) ? (json_decode($stored->layout_settings, true) ?: []) : ($stored->layout_settings ?? []),
            ];
        }

        uasort($result, fn ($a, $b) => ((int)($a['sort_order'] ?? 0)) <=> ((int)($b['sort_order'] ?? 0)));

        return $result;
    }
    
    private function getIslamicDate($date)
    {
        // Convert to Saudi Arabia timezone (UTC+3)
        $date = $date->copy()->setTimezone('Asia/Riyadh');
        
        $gregorianDate = $date->format('Y-m-d');
        $cacheKey = "islamic_date_" . $gregorianDate;
        
        // Check cache first
        $cached = cache()->get($cacheKey);
        if ($cached) {
            return $cached;
        }
        
        // Islamic months names
        $islamicMonths = [
            1 => 'Muharram',
            2 => 'Safar',
            3 => 'Rabiʻ al-Awwal',
            4 => 'Rabiʻ al-Thani',
            5 => 'Jumada al-Awwal',
            6 => 'Jumada al-Thani',
            7 => 'Rajab',
            8 => 'Shaʻban',
            9 => 'Ramadan',
            10 => 'Shawwal',
            11 => 'Dhu al-Qiʻdah',
            12 => 'Dhu al-Hijjah'
        ];
        
        $hijri = $this->getHijriFromUmmAlQuraCalendar($date);

        // Fallback to Aladhan API (best-effort)
        if (!$hijri) {
            $hijri = $this->getHijriFromPrayerAPI($date);
        }
        
        // If API fails, use local calculation
        if (!$hijri) {
            $hijri = $this->gregorianToHijri(
                (int)$date->format('Y'),
                (int)$date->format('m'),
                (int)$date->format('d')
            );
        }
        
        $result = [
            'day' => (string)$hijri['day'],
            'month' => $islamicMonths[$hijri['month']] ?? 'Unknown',
            'year' => (string)$hijri['year']
        ];
        
        // Cache for 24 hours
        cache()->put($cacheKey, $result, 86400);
        
        return $result;
    }

    private function getHijriFromUmmAlQuraCalendar($date): ?array
    {
        if (!class_exists(\IntlCalendar::class)) {
            return null;
        }

        try {
            $timezone = $date->getTimezone()->getName();
            $calendar = \IntlCalendar::createInstance($timezone, 'en_US@calendar=islamic-umalqura');

            if (!$calendar) {
                return null;
            }

            $calendar->setTime(((int) $date->getTimestamp()) * 1000);

            $day = (int) $calendar->get(\IntlCalendar::FIELD_DAY_OF_MONTH);
            $monthZeroBased = (int) $calendar->get(\IntlCalendar::FIELD_MONTH);
            $year = (int) $calendar->get(\IntlCalendar::FIELD_YEAR);

            $month = $monthZeroBased + 1;
            if ($day < 1 || $month < 1 || $month > 12 || $year < 1) {
                return null;
            }

            return [
                'day' => $day,
                'month' => $month,
                'year' => $year,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
    
    private function getHijriFromPrayerAPI($date)
    {
        try {
            // Use Aladhan prayer times API which includes Islamic date
            $dateStr = $date->format('d-m-Y');
            $url = "https://api.aladhan.com/v1/gToH?date=" . $dateStr;
            
            $context = stream_context_create([
                'http' => ['timeout' => 3, 'user_agent' => 'Mozilla/5.0'],
                'https' => ['timeout' => 3]
            ]);
            
            $response = @file_get_contents($url, false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                
                if (isset($data['data']['hijri']) && is_array($data['data']['hijri'])) {
                    $hijri = $data['data']['hijri'];
                    
                    // API returns month as object with 'number' key or as integer
                    $month = is_array($hijri['month']) ? (int)$hijri['month']['number'] : (int)$hijri['month'];
                    
                    return [
                        'day' => (int)$hijri['day'],
                        'month' => $month,
                        'year' => (int)$hijri['year']
                    ];
                }
            }
        } catch (\Exception $e) {
            // Silently continue to local calculation
        }
        
        return null;
    }
    
    private function gregorianToHijri($gy, $gm, $gd)
    {
        // Simple conversion - days since epoch
        $epoch = 1948440; // Julian day for 1/1/1 AH
        
        // Calculate Julian day from Gregorian date
        $a = (int)(((14 - $gm) / 12));
        $y = $gy + 4800 - $a;
        $m = $gm + 12 * $a - 3;
        
        $jd = $gd + (int)(((153 * $m + 2) / 5)) + 365 * $y + (int)(($y / 4)) - (int)(($y / 100)) + (int)(($y / 400)) - 32045;
        
        // Calculate Islamic date from days since epoch
        $days = $jd - $epoch;
        
        // Islamic year (approximate)
        $iy = (int)(($days * 30) / 10631) + 1;
        
        // Days into Islamic year  
        $month_num = (int)((($days % 10631) * 12) / 10631) + 1;
        if ($month_num > 12) $month_num = 12;
        
        // Day of month
        $days_per_month = ($month_num % 2 == 1) ? 30 : 29;
        $id = (int)(($days % (int)(10631 / 12))) + 1;
        if ($id > $days_per_month) {
            $id = $days_per_month;
        }
        
        // Ensure valid values
        if ($iy < 1) $iy = 1;
        if ($month_num < 1) $month_num = 1;
        if ($month_num > 12) $month_num = 12;
        if ($id < 1) $id = 1;
        if ($id > 30) $id = 30;

        return ['day' => $id, 'month' => $month_num, 'year' => $iy];
    }
}
