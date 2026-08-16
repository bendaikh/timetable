<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\BoxSetting;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Models\SlidingText;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DisplayStateVersionService
{
    public function getVersions(string $mediaRuntimeSignature = '', string $screenState = ''): array
    {
        return [
            'announcements_version' => $this->getAnnouncementsVersion(),
            'sliding_texts_version' => $this->getSlidingTextsVersion(),
            'media_version' => $this->getMediaVersion($mediaRuntimeSignature),
            'timetable_version' => $this->getTimetableVersion(),
            'config_version' => $this->getConfigVersion(),
            'state_version' => $this->getStateVersion($screenState), // NEW: Track state changes
        ];
    }

    public function getAnnouncementsVersion(): string
    {
        $activeAnnouncementsFingerprint = Announcement::getActiveAnnouncements()
            ->map(function ($announcement) {
                $updatedAt = $announcement->updated_at ? $announcement->updated_at->timestamp : 0;
                return "{$announcement->id}:{$updatedAt}:{$announcement->scroll_speed}:{$announcement->display_duration}:{$announcement->display_order}";
            })
            ->implode('|');

        $maxUpdatedAt = Announcement::max('updated_at');
        $maxUpdatedAtTs = $maxUpdatedAt ? Carbon::parse($maxUpdatedAt)->timestamp : 0;

        return sha1("announcements|{$maxUpdatedAtTs}|{$activeAnnouncementsFingerprint}");
    }

    public function getSlidingTextsVersion(): string
    {
        $activeTextsFingerprint = SlidingText::getActiveTexts()
            ->map(function ($text) {
                $updatedAt = $text->updated_at ? $text->updated_at->timestamp : 0;

                return implode(':', [
                    $text->id,
                    $updatedAt,
                    $text->font_size,
                    $text->font_weight,
                    $text->text_color,
                    $text->animation_speed,
                    $text->display_order,
                    md5((string) $text->text),
                ]);
            })
            ->implode('|');

        $maxUpdatedAt = SlidingText::max('updated_at');
        $maxUpdatedAtTs = $maxUpdatedAt ? Carbon::parse($maxUpdatedAt)->timestamp : 0;

        return sha1("sliding_texts|{$maxUpdatedAtTs}|{$activeTextsFingerprint}");
    }

    public function getTimetableVersion(): string
    {
        $today = Carbon::today()->toDateString();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $prayerMaxUpdated = PrayerTime::whereDate('date', $today)
            ->orWhereDate('date', $tomorrow)
            ->max('updated_at');

        $prayerMaxUpdatedTs = $prayerMaxUpdated ? Carbon::parse($prayerMaxUpdated)->timestamp : 0;

        $settingsMaxUpdated = Setting::whereIn('key', [
            'fajr_jamaat_offset',
            'zohar_jamaat_offset',
            'asr_jamaat_offset',
            'maghrib_jamaat_offset',
            'isha_jamaat_offset',
            'adhan_countdown_duration',
            'media_display_enabled',
        ])->max('updated_at');

        $settingsMaxUpdatedTs = $settingsMaxUpdated ? Carbon::parse($settingsMaxUpdated)->timestamp : 0;

        return sha1("timetable|{$today}|{$tomorrow}|{$prayerMaxUpdatedTs}|{$settingsMaxUpdatedTs}");
    }

    public function getMediaVersion(string $runtimeSignature = ''): string
    {
        $mediaMaxUpdated = DB::table('media')->max('updated_at');
        $mediaSchedulesMaxUpdated = DB::table('media_schedules')->max('updated_at');
        $pivotMaxUpdated = DB::table('media_schedule_media')->max('updated_at');
        $todayPrayerMaxUpdated = PrayerTime::whereDate('date', Carbon::today())->max('updated_at');

        $settingsMaxUpdated = Setting::whereIn('key', [
            'media_display_enabled',
            'adhan_countdown_duration',
            'fajr_jamaat_offset',
            'zohar_jamaat_offset',
            'asr_jamaat_offset',
            'maghrib_jamaat_offset',
            'isha_jamaat_offset',
        ])->max('updated_at');

        $mediaMaxUpdatedTs = $mediaMaxUpdated ? Carbon::parse($mediaMaxUpdated)->timestamp : 0;
        $mediaSchedulesMaxUpdatedTs = $mediaSchedulesMaxUpdated ? Carbon::parse($mediaSchedulesMaxUpdated)->timestamp : 0;
        $pivotMaxUpdatedTs = $pivotMaxUpdated ? Carbon::parse($pivotMaxUpdated)->timestamp : 0;
        $todayPrayerMaxUpdatedTs = $todayPrayerMaxUpdated ? Carbon::parse($todayPrayerMaxUpdated)->timestamp : 0;
        $settingsMaxUpdatedTs = $settingsMaxUpdated ? Carbon::parse($settingsMaxUpdated)->timestamp : 0;

        return sha1(
            "media|{$mediaMaxUpdatedTs}|{$mediaSchedulesMaxUpdatedTs}|{$pivotMaxUpdatedTs}|{$todayPrayerMaxUpdatedTs}|{$settingsMaxUpdatedTs}|{$runtimeSignature}"
        );
    }

    public function getConfigVersion(): string
    {
        $boxesFingerprint = BoxSetting::orderBy('box_type')
            ->get()
            ->map(function ($box) {
                $updatedAt = $box->updated_at ? $box->updated_at->timestamp : 0;
                $payload = json_encode([
                    $box->styling_settings,
                    $box->layout_settings,
                    $box->content_settings,
                    $box->is_active,
                    $box->sort_order,
                    $box->box_name,
                ]);

                return "{$box->box_type}:{$updatedAt}:" . md5($payload ?: '');
            })
            ->implode('|');

        $settingsMaxUpdated = Setting::max('updated_at');
        $settingsMaxUpdatedTs = $settingsMaxUpdated ? Carbon::parse($settingsMaxUpdated)->timestamp : 0;
        $slidingTextsVersion = $this->getSlidingTextsVersion();

        return sha1("screen_config|{$boxesFingerprint}|{$settingsMaxUpdatedTs}|{$slidingTextsVersion}");
    }

    /**
     * Generate version hash for screen state (ADHAN, COUNTDOWN, TIMETABLE, POSTER, etc.)
     * This ensures frontend re-renders when state changes, not just when media content changes
     */
    public function getStateVersion(string $screenState = ''): string
    {
        if (!$screenState) {
            $screenState = 'TIMETABLE';
        }
        
        return sha1("state|{$screenState}");
    }
}
