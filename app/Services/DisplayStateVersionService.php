<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\PrayerTime;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DisplayStateVersionService
{
    public function getVersions(string $mediaRuntimeSignature = '', string $screenState = ''): array
    {
        return [
            'announcements_version' => $this->getAnnouncementsVersion(),
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
                return "{$announcement->id}:{$updatedAt}";
            })
            ->implode('|');

        $maxUpdatedAt = Announcement::max('updated_at');
        $maxUpdatedAtTs = $maxUpdatedAt ? Carbon::parse($maxUpdatedAt)->timestamp : 0;

        return sha1("announcements|{$maxUpdatedAtTs}|{$activeAnnouncementsFingerprint}");
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
        $boxSettingsMaxUpdated = DB::table('box_settings')->max('updated_at');
        $settingsMaxUpdated = Setting::max('updated_at');

        $boxSettingsMaxUpdatedTs = $boxSettingsMaxUpdated ? Carbon::parse($boxSettingsMaxUpdated)->timestamp : 0;
        $settingsMaxUpdatedTs = $settingsMaxUpdated ? Carbon::parse($settingsMaxUpdated)->timestamp : 0;

        return sha1("screen_config|{$boxSettingsMaxUpdatedTs}|{$settingsMaxUpdatedTs}");
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
