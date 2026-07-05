<?php

namespace App\Http\Controllers;

use App\Services\MediaDisplayService;
use App\Services\DisplayStateVersionService;
use App\Support\PrayerCountdownWindows;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MediaDisplayController extends Controller
{
    protected MediaDisplayService $mediaDisplayService;
    protected DisplayStateVersionService $displayStateVersionService;

    public function __construct(MediaDisplayService $mediaDisplayService, DisplayStateVersionService $displayStateVersionService)
    {
        $this->mediaDisplayService = $mediaDisplayService;
        $this->displayStateVersionService = $displayStateVersionService;
    }

    /**
     * Get current media to display
     */
    public function getCurrentMedia(): JsonResponse
    {
        if (!$this->mediaDisplayService->isMediaDisplayEnabled()) {
            return response()->json($this->withVersions(['media' => null], 'MEDIA_DISABLED'));
        }

        $mediaInfo = $this->mediaDisplayService->getCurrentMedia();
        
        if (!$mediaInfo) {
            return response()->json($this->withVersions(['media' => null], 'NO_MEDIA'));
        }

        $media = $mediaInfo['media'];
        $duration = $mediaInfo['duration'];

        $payload = [
            'media' => [
                'id' => $media->id,
                'title' => $media->title,
                'type' => $media->type,
                'file_url' => $media->file_url,
                'display_duration' => $duration, // Use duration from pivot table
                'description' => $media->description,
                'priority' => $mediaInfo['priority'],
                'schedule_id' => $mediaInfo['schedule']->id ?? null
            ]
        ];

        return response()->json($this->withVersions($payload, $this->buildMediaSignatureForCurrentMedia($payload['media'])));
    }

    /**
     * Get countdown information
     */
    public function getCountdownInfo(): JsonResponse
    {
        $countdownInfo = $this->mediaDisplayService->getCountdownInfo();
        
        $payload = [
            'countdown' => $countdownInfo
        ];

        return response()->json($this->withVersions($payload, $this->buildMediaSignatureForCountdown($countdownInfo)));
    }

    /**
     * Get media display status
     */
    public function getStatus(): JsonResponse
    {
        $payload = [
            'enabled' => $this->mediaDisplayService->isMediaDisplayEnabled(),
            'current_media' => $this->mediaDisplayService->getCurrentMedia(),
            'countdown_info' => $this->mediaDisplayService->getCountdownInfo()
        ];

        $signature = $this->buildMediaSignatureForStatus($payload);
        return response()->json($this->withVersions($payload, $signature));
    }

    /**
     * Debug endpoint to see priority logic
     */
    public function debugPriority(): JsonResponse
    {
        $now = $this->mediaDisplayService->currentTime();
        $today = $now->format('Y-m-d');
        
        // Check prayer times
        $prayerTimes = \App\Models\PrayerTime::whereDate('date', $today)->first();
        $countdownDuration = PrayerCountdownWindows::DURATION_SECONDS;
        $adhanLeadSeconds = PrayerCountdownWindows::ADHAN_LEAD_SECONDS;
        $iqamahLeadSeconds = PrayerCountdownWindows::IQAMAH_LEAD_SECONDS;
        
        $debugInfo = [
            'current_time' => $now->format('H:i:s'),
            'countdown_duration' => $countdownDuration . ' seconds',
            'adhan_countdown_lead' => ($adhanLeadSeconds / 60) . ' minutes before jamaat (iqamah)',
            'iqamah_countdown_lead' => $iqamahLeadSeconds . ' seconds before iqamah',
            'adhan_countdown_active' => $this->mediaDisplayService->isAdhanOrCountdownActive($now),
            'current_media' => $this->mediaDisplayService->getCurrentMedia(),
            'prayer_times' => null,
            'countdown_windows' => []
        ];
        
        if ($prayerTimes) {
            $prayers = [
                'fajr' => $prayerTimes->fajr,
                'zohar' => $prayerTimes->zohar,
                'asr' => $prayerTimes->asr,
                'maghrib' => $prayerTimes->maghrib,
                'isha' => $prayerTimes->isha,
            ];
            
            $debugInfo['prayer_times'] = $prayers;
            
            foreach ($prayers as $name => $time) {
                $jamaatField = $name . '_jamaat';
                $adhanField = $name . '_adhan';
                $iqamahTime = \Carbon\Carbon::parse($time);
                if (!empty($prayerTimes->$jamaatField)) {
                    $iqamahTime = \Carbon\Carbon::parse($prayerTimes->$jamaatField);
                } else {
                    $offset = (int) \App\Models\Setting::get($name . '_jamaat_offset', 0);
                    $iqamahTime = \Carbon\Carbon::parse($time)->addMinutes($offset);
                }

                $schedule = PrayerCountdownWindows::windowSchedule($iqamahTime);

                $debugInfo['countdown_windows'][] = [
                    'prayer' => $name,
                    'beginning_time' => $time,
                    'adhan_time' => $prayerTimes->$adhanField ?? null,
                    'jamaat_time' => $prayerTimes->$jamaatField ?? null,
                    'countdown_target' => 'jamaat',
                    'resolved_jamaat_time' => $iqamahTime->format('H:i:s'),
                    'adhan_countdown_start' => \Carbon\Carbon::parse($schedule['adhan_countdown']['start'])->format('H:i:s'),
                    'adhan_countdown_end' => \Carbon\Carbon::parse($schedule['adhan_countdown']['end'])->format('H:i:s'),
                    'iqamah_countdown_start' => \Carbon\Carbon::parse($schedule['iqamah_countdown']['start'])->format('H:i:s'),
                    'iqamah_countdown_end' => \Carbon\Carbon::parse($schedule['iqamah_countdown']['end'])->format('H:i:s'),
                    'adhan_countdown_active' => $now->between(
                        \Carbon\Carbon::parse($schedule['adhan_countdown']['start']),
                        \Carbon\Carbon::parse($schedule['adhan_countdown']['end']),
                        false
                    ),
                    'iqamah_countdown_active' => $now->gte(\Carbon\Carbon::parse($schedule['iqamah_countdown']['start']))
                        && $now->lt(\Carbon\Carbon::parse($schedule['iqamah_countdown']['end'])),
                ];
            }
        }
        
        return response()->json($debugInfo);
    }

    /**
     * Debug endpoint to see active schedules
     */
    public function debugSchedules(): JsonResponse
    {
        return response()->json([
            'active_schedules' => $this->mediaDisplayService->getActiveSchedules(),
            'current_media_info' => $this->mediaDisplayService->getCurrentMedia()
        ]);
    }

    public function getCountdownDiagnostic(): JsonResponse
    {
        $diagnostic = $this->mediaDisplayService->getCountdownDiagnostic();

        if ($diagnostic['log']['countdown_active'] ?? false) {
            Log::info('prayer.countdown.active', $diagnostic['log']);
        }

        return response()->json($diagnostic);
    }

    /**
     * GET THE UNIFIED SCREEN STATE
     * Returns exactly ONE state at a time: ADHAN, COUNTDOWN, PRAYER_POSTER, FULLTIME_POSTER, or TIMETABLE
     */
    public function getScreenState(): JsonResponse
    {
        $now = $this->mediaDisplayService->currentTime();
        $today = $now->format('Y-m-d');
        
        // 1. Check if a prayer countdown window is active (HIGHEST PRIORITY)
        if ($this->mediaDisplayService->isAdhanOrCountdownActive($now)) {
            $countdownInfo = $this->mediaDisplayService->getCountdownInfo($now);
            $formattedCountdown = $this->mediaDisplayService->formatCountdownForApi($countdownInfo);

            $payload = [
                'state' => 'COUNTDOWN',
                'countdown' => $formattedCountdown,
                'timestamp' => $now->toIso8601String(),
                'app_timezone' => $this->mediaDisplayService->getAppTimezone(),
            ];

            Log::info('prayer.countdown.active', [
                'server_time' => $now->toIso8601String(),
                'target_prayer' => $formattedCountdown['prayer_name'] ?? null,
                'countdown_phase' => $formattedCountdown['phase'] ?? null,
                'target_field' => $formattedCountdown['target_field'] ?? null,
                'target_time' => $formattedCountdown['target_time'] ?? null,
                'countdown_start' => $formattedCountdown['countdown_start'] ?? null,
                'countdown_end' => $formattedCountdown['countdown_end'] ?? null,
                'seconds_remaining' => $formattedCountdown['seconds_remaining'] ?? null,
                'message' => $formattedCountdown['message'] ?? null,
            ]);

            $stateSignature = $this->buildMediaSignatureForScreenState([
                'state' => 'COUNTDOWN',
                'countdown' => $countdownInfo,
            ]);
            return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
        }
        
        // 2. Check for PRAYER_POSTER (Before/After Prayer schedules) (MEDIUM PRIORITY)
        $prayerPosterMedia = $this->mediaDisplayService->getCurrentMedia();
        if ($prayerPosterMedia && isset($prayerPosterMedia['schedule'])) {
            $schedule = $prayerPosterMedia['schedule'];
            if ($schedule->schedule_type !== 'full_time_poster') {
                $payload = [
                    'state' => 'PRAYER_POSTER',
                    'media' => [
                        'id' => $prayerPosterMedia['media']->id,
                        'title' => $prayerPosterMedia['media']->title,
                        'type' => $prayerPosterMedia['media']->type,
                        'file_url' => $prayerPosterMedia['media']->file_url,
                        'display_duration' => $prayerPosterMedia['duration'],
                        'description' => $prayerPosterMedia['media']->description,
                        'priority' => $prayerPosterMedia['priority'],
                        'schedule_id' => $schedule->id
                    ],
                    'timestamp' => $now->toIso8601String()
                ];

                $stateSignature = $this->buildMediaSignatureForScreenState($payload);
                return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
            }
        }
        
        // 3. Check for FULLTIME_POSTER (LOWEST PRIORITY)
        if ($prayerPosterMedia && isset($prayerPosterMedia['schedule'])) {
            $schedule = $prayerPosterMedia['schedule'];
            if ($schedule->schedule_type === 'full_time_poster') {
                $payload = [
                    'state' => 'FULLTIME_POSTER',
                    'media' => [
                        'id' => $prayerPosterMedia['media']->id,
                        'title' => $prayerPosterMedia['media']->title,
                        'type' => $prayerPosterMedia['media']->type,
                        'file_url' => $prayerPosterMedia['media']->file_url,
                        'display_duration' => $prayerPosterMedia['duration'],
                        'description' => $prayerPosterMedia['media']->description,
                        'priority' => $prayerPosterMedia['priority'],
                        'schedule_id' => $schedule->id
                    ],
                    'timestamp' => $now->toIso8601String()
                ];

                $stateSignature = $this->buildMediaSignatureForScreenState($payload);
                return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
            }
        }
        
        // 4. Default: TIMETABLE (show the main timetable screen)
        $payload = [
            'state' => 'TIMETABLE',
            'timestamp' => $now->toIso8601String(),
            'app_timezone' => $this->mediaDisplayService->getAppTimezone(),
        ];

        $stateSignature = $this->buildMediaSignatureForScreenState($payload);
        return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
    }

    private function withVersions(array $payload, string $mediaRuntimeSignature = '', string $screenState = ''): array
    {
        return [
            ...$payload,
            ...$this->displayStateVersionService->getVersions($mediaRuntimeSignature, $screenState),
        ];
    }

    private function buildMediaSignatureForCurrentMedia(?array $media): string
    {
        if (!$media) {
            return 'CURRENT_MEDIA:null';
        }

        return sprintf(
            'CURRENT_MEDIA:%s:%s:%s:%s',
            $media['id'] ?? 'null',
            $media['schedule_id'] ?? 'null',
            $media['priority'] ?? 'null',
            $media['display_duration'] ?? 'null'
        );
    }

    private function buildMediaSignatureForCountdown(?array $countdown): string
    {
        if (!$countdown) {
            return 'COUNTDOWN:null';
        }

        return sprintf(
            'COUNTDOWN:%s:%s:%s:%s',
            $countdown['prayer_name'] ?? 'null',
            $countdown['phase'] ?? 'null',
            isset($countdown['countdown_end']) ? (string) $countdown['countdown_end'] : 'null',
            (($countdown['is_countdown_time'] ?? false) ? '1' : '0')
        );
    }

    private function buildMediaSignatureForStatus(array $statusPayload): string
    {
        $currentMedia = $statusPayload['current_media'] ?? null;
        $countdownInfo = $statusPayload['countdown_info'] ?? null;

        $currentMediaId = isset($currentMedia['media']) ? ($currentMedia['media']->id ?? 'null') : 'null';
        $currentScheduleId = isset($currentMedia['schedule']) ? ($currentMedia['schedule']->id ?? 'null') : 'null';
        $countdownPrayer = $countdownInfo['prayer_name'] ?? 'null';
        $countdownState = (($countdownInfo['is_countdown_time'] ?? false) ? '1' : '0');

        return sprintf(
            'STATUS:%s:%s:%s:%s:%s',
            $statusPayload['enabled'] ? '1' : '0',
            $currentMediaId,
            $currentScheduleId,
            $countdownPrayer,
            $countdownState
        );
    }

    private function buildMediaSignatureForScreenState(array $payload): string
    {
        $state = $payload['state'] ?? 'TIMETABLE';

        if ($state === 'COUNTDOWN') {
            $countdown = $payload['countdown'] ?? [];

            return sprintf(
                'SCREEN_STATE:COUNTDOWN:%s:%s:%s:%s',
                $countdown['prayer_name'] ?? 'null',
                $countdown['phase'] ?? 'null',
                isset($countdown['countdown_end']) ? (string) $countdown['countdown_end'] : 'null',
                (($countdown['is_countdown_time'] ?? false) ? '1' : '0')
            );
        }

        if ($state === 'ADHAN') {
            return 'SCREEN_STATE:ADHAN';
        }

        if (in_array($state, ['PRAYER_POSTER', 'FULLTIME_POSTER'], true)) {
            $media = $payload['media'] ?? [];

            return sprintf(
                'SCREEN_STATE:%s:%s:%s:%s',
                $state,
                $media['id'] ?? 'null',
                $media['schedule_id'] ?? 'null',
                $media['priority'] ?? 'null'
            );
        }

        return 'SCREEN_STATE:TIMETABLE';
    }
}
