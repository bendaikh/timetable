<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerTime;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\BoxSetting;
use App\Models\SlidingText;
use App\Services\DisplayStateVersionService;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function __construct(private DisplayStateVersionService $displayStateVersionService)
    {
    }

    public function timetable()
    {
        $timezone = (string) (Setting::get('timezone', config('app.timezone')) ?: config('app.timezone'));
        $now = Carbon::now($timezone);

        $todayPrayerTimes = PrayerTime::getTodayPrayerTimes();
        $tomorrowPrayerTimes = PrayerTime::getTomorrowPrayerTimes();

        return response()->json([
            'server_date' => $now->toDateString(),
            'islamic_date' => $this->getIslamicDate($now),
            'today' => $this->formatPrayerTimeRecord($todayPrayerTimes),
            'tomorrow' => $this->formatPrayerTimeRecord($tomorrowPrayerTimes),
            'jamaat_offsets' => [
                'fajr' => (int) Setting::get('fajr_jamaat_offset', 10),
                'zohar' => (int) Setting::get('zohar_jamaat_offset', 15),
                'asr' => (int) Setting::get('asr_jamaat_offset', 20),
                'maghrib' => (int) Setting::get('maghrib_jamaat_offset', 0),
                'isha' => (int) Setting::get('isha_jamaat_offset', 10),
            ],
            ...$this->displayStateVersionService->getVersions(),
        ]);
    }

    public function screenConfig()
    {
        $defaults = BoxSetting::getDefaultBoxSettings();
        $boxes = BoxSetting::orderBy('sort_order')->get();
        $boxSettings = [];
        $boxOrder = [];

        foreach ($boxes as $box) {
            $boxOrder[] = $box->box_type;
            $boxSettings[$box->box_type] = [
                'box_type' => $box->box_type,
                'box_name' => $box->box_name,
                'is_active' => (bool) $box->is_active,
                'sort_order' => (int) $box->sort_order,
                'content_settings' => is_string($box->content_settings) ? json_decode($box->content_settings, true) : ($box->content_settings ?? []),
                'styling_settings' => is_string($box->styling_settings) ? json_decode($box->styling_settings, true) : ($box->styling_settings ?? []),
                'layout_settings' => is_string($box->layout_settings) ? json_decode($box->layout_settings, true) : ($box->layout_settings ?? []),
                'updated_at' => $box->updated_at ? $box->updated_at->toIso8601String() : null,
            ];
        }

        foreach ($defaults as $boxType => $default) {
            if (isset($boxSettings[$boxType])) {
                continue;
            }

            $boxOrder[] = $boxType;
            $boxSettings[$boxType] = [
                'box_type' => $boxType,
                'box_name' => $default['box_name'] ?? $boxType,
                'is_active' => true,
                'sort_order' => count($boxOrder) - 1,
                'content_settings' => $default['content_settings'] ?? [],
                'styling_settings' => $default['styling_settings'] ?? [],
                'layout_settings' => $default['layout_settings'] ?? [],
                'updated_at' => null,
            ];
        }

        $themeVariables = $this->getThemeVariables();

        return response()->json([
            'config_version' => $this->displayStateVersionService->getConfigVersion(),
            'theme_variables' => $themeVariables,
            'box_settings' => $boxSettings,
            'box_order' => $boxOrder,
            'sliding_texts' => SlidingText::getActiveTexts(),
            ...$this->displayStateVersionService->getVersions(),
        ]);
    }

    public function prayerTimes()
    {
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        return response()->json($prayerTimes);
    }

    public function tomorrowPrayerTimes()
    {
        $prayerTimes = PrayerTime::getTomorrowPrayerTimes();
        return response()->json($prayerTimes);
    }

    public function announcements()
    {
        $announcements = Announcement::getActiveAnnouncements();

        return response()->json([
            'announcements' => $announcements,
            ...$this->displayStateVersionService->getVersions(),
        ]);
    }

    public function slidingTexts()
    {
        return response()->json([
            'sliding_texts' => SlidingText::getActiveTexts(),
            ...$this->displayStateVersionService->getVersions(),
        ]);
    }

    /**
     * Next prayer countdown payload.
     *
     * Canonical definition: next JAMAAT (iqamah) via PrayerTime::getNextPrayer()
     * / PrayerJamaatTime::resolve — same as the TV "Next prayer in" label.
     * Field used: {prayer}_jamaat when set, else beginning + {prayer}_jamaat_offset.
     */
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
            'auto_refresh_interval' => Setting::get('auto_refresh_interval', '60'),
        ];
        
        return response()->json($settings);
    }

    private function formatPrayerTimeRecord(?PrayerTime $record): ?array
    {
        if (!$record) {
            return null;
        }

        return [
            'date' => $record->date ? Carbon::parse($record->date)->toDateString() : null,
            'fajr' => $this->normalizeTime($record->fajr),
            'fajr_adhan' => $this->normalizeTime($record->fajr_adhan),
            'fajr_jamaat' => $this->normalizeTime($record->fajr_jamaat),
            'zohar' => $this->normalizeTime($record->zohar),
            'zohar_adhan' => $this->normalizeTime($record->zohar_adhan),
            'zohar_jamaat' => $this->normalizeTime($record->zohar_jamaat),
            'asr' => $this->normalizeTime($record->asr),
            'asr_adhan' => $this->normalizeTime($record->asr_adhan),
            'asr_jamaat' => $this->normalizeTime($record->asr_jamaat),
            'maghrib' => $this->normalizeTime($record->maghrib),
            'maghrib_adhan' => $this->normalizeTime($record->maghrib_adhan),
            'maghrib_jamaat' => $this->normalizeTime($record->maghrib_jamaat),
            'isha' => $this->normalizeTime($record->isha),
            'isha_adhan' => $this->normalizeTime($record->isha_adhan),
            'isha_jamaat' => $this->normalizeTime($record->isha_jamaat),
            'sun_rise' => $this->normalizeTime($record->sun_rise),
            'jumah_1' => $this->normalizeTime($record->jumah_1),
            'jumah_2' => $this->normalizeTime($record->jumah_2),
            'eid_prayer_1' => $this->normalizeTime($record->eid_prayer_1),
            'eid_prayer_2' => $this->normalizeTime($record->eid_prayer_2),
        ];
    }

    private function normalizeTime($value): ?string
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value)->format('H:i');
    }

    private function getThemeVariables(): array
    {
        $defaults = [
            'display_font_family' => 'Arial, sans-serif',
            'display_background_color' => '#ffffff',
            'display_text_color' => '#000000',
            'prayer_time_font_size' => '24',
            'announcement_scroll_speed' => '3',
            'use_boxes_styling' => 'enabled',
            'logo_path' => null,
            'auto_refresh_interval' => '60',
            'hadeeth_display_duration' => '30',
        ];

        $themeSettings = Setting::all()
            ->mapWithKeys(fn ($setting) => [$setting->key => Setting::get($setting->key)])
            ->toArray();

        return array_merge($defaults, $themeSettings);
    }

    private function getIslamicDate(Carbon $date): array
    {
        $date = $date->copy()->setTimezone('Asia/Riyadh');
        $gregorianDate = $date->format('Y-m-d');
        $cacheKey = "islamic_date_{$gregorianDate}";

        $cached = cache()->get($cacheKey);
        if ($cached) {
            return $cached;
        }

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
            12 => 'Dhu al-Hijjah',
        ];

        $hijri = $this->getHijriFromUmmAlQuraCalendar($date);
        if (!$hijri) {
            $hijri = $this->getHijriFromPrayerAPI($date);
        }
        if (!$hijri) {
            $hijri = $this->gregorianToHijri(
                (int) $date->format('Y'),
                (int) $date->format('m'),
                (int) $date->format('d')
            );
        }

        $result = [
            'day' => (string) $hijri['day'],
            'month' => $islamicMonths[$hijri['month']] ?? 'Unknown',
            'year' => (string) $hijri['year'],
        ];

        cache()->put($cacheKey, $result, 86400);

        return $result;
    }

    private function getHijriFromUmmAlQuraCalendar(Carbon $date): ?array
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

    private function getHijriFromPrayerAPI(Carbon $date): ?array
    {
        try {
            $dateStr = $date->format('d-m-Y');
            $url = "https://api.aladhan.com/v1/gToH?date={$dateStr}";

            $context = stream_context_create([
                'http' => ['timeout' => 3, 'user_agent' => 'Mozilla/5.0'],
                'https' => ['timeout' => 3],
            ]);

            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                return null;
            }

            $data = json_decode($response, true);
            if (!isset($data['data']['hijri']) || !is_array($data['data']['hijri'])) {
                return null;
            }

            $hijri = $data['data']['hijri'];
            $month = is_array($hijri['month']) ? (int) $hijri['month']['number'] : (int) $hijri['month'];

            return [
                'day' => (int) $hijri['day'],
                'month' => $month,
                'year' => (int) $hijri['year'],
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function gregorianToHijri(int $gy, int $gm, int $gd): array
    {
        $epoch = 1948440;
        $a = (int) ((14 - $gm) / 12);
        $y = $gy + 4800 - $a;
        $m = $gm + 12 * $a - 3;

        $jd = $gd + (int) ((153 * $m + 2) / 5) + 365 * $y + (int) ($y / 4) - (int) ($y / 100) + (int) ($y / 400) - 32045;
        $days = $jd - $epoch;

        $iy = (int) (($days * 30) / 10631) + 1;
        $monthNum = (int) ((($days % 10631) * 12) / 10631) + 1;
        if ($monthNum > 12) {
            $monthNum = 12;
        }

        $daysPerMonth = ($monthNum % 2 === 1) ? 30 : 29;
        $id = (int) (($days % (int) (10631 / 12))) + 1;
        if ($id > $daysPerMonth) {
            $id = $daysPerMonth;
        }

        if ($iy < 1) {
            $iy = 1;
        }
        if ($monthNum < 1) {
            $monthNum = 1;
        }
        if ($monthNum > 12) {
            $monthNum = 12;
        }
        if ($id < 1) {
            $id = 1;
        }
        if ($id > 30) {
            $id = 30;
        }

        return ['day' => $id, 'month' => $monthNum, 'year' => $iy];
    }
}
