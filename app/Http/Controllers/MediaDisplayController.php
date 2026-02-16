<?php

namespace App\Http\Controllers;

use App\Services\MediaDisplayService;
use App\Services\DisplayStateVersionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
        $now = \Carbon\Carbon::now();
        $today = $now->format('Y-m-d');
        
        // Check prayer times
        $prayerTimes = \App\Models\PrayerTime::whereDate('date', $today)->first();
        $countdownDuration = (int) \App\Models\Setting::get('adhan_countdown_duration', 30);
        
        $debugInfo = [
            'current_time' => $now->format('H:i:s'),
            'countdown_duration' => $countdownDuration . ' seconds',
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
                $prayerTime = \Carbon\Carbon::parse($time);
                $countdownStart = $prayerTime->copy()->subSeconds($countdownDuration);
                $countdownEnd = $prayerTime->copy()->addSeconds(5);
                
                $inCountdown = $now->isBetween($countdownStart, $countdownEnd);
                
                $debugInfo['countdown_windows'][] = [
                    'prayer' => $name,
                    'adhan_time' => $prayerTime->format('H:i:s'),
                    'countdown_start' => $countdownStart->format('H:i:s'),
                    'countdown_end' => $countdownEnd->format('H:i:s'),
                    'currently_active' => $inCountdown
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

    /**
     * GET THE UNIFIED SCREEN STATE
     * Returns exactly ONE state at a time: ADHAN, COUNTDOWN, PRAYER_POSTER, FULLTIME_POSTER, or TIMETABLE
     */
    public function getScreenState(): JsonResponse
    {
        $now = \Carbon\Carbon::now();
        $today = $now->format('Y-m-d');
        
        // 1. Check if ADHAN or COUNTDOWN is active (HIGHEST PRIORITY)
        if ($this->mediaDisplayService->isAdhanOrCountdownActive($now)) {
            $countdownInfo = $this->mediaDisplayService->getCountdownInfo();
            
            if ($countdownInfo && $countdownInfo['is_countdown_time']) {
                // COUNTDOWN STATE
                $payload = [
                    'state' => 'COUNTDOWN',
                    'countdown' => $countdownInfo,
                    'timestamp' => $now->toIso8601String()
                ];

                $stateSignature = $this->buildMediaSignatureForScreenState($payload);
                return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
            } else {
                // ADHAN STATE (during/after prayer time)
                $payload = [
                    'state' => 'ADHAN',
                    'timestamp' => $now->toIso8601String()
                ];

                $stateSignature = $this->buildMediaSignatureForScreenState($payload);
                return response()->json($this->withVersions($payload, $stateSignature, $stateSignature));
            }
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
            'timestamp' => $now->toIso8601String()
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
            'COUNTDOWN:%s:%s:%s',
            $countdown['prayer_name'] ?? 'null',
            isset($countdown['prayer_time']) ? (string) $countdown['prayer_time'] : 'null',
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
                'SCREEN_STATE:COUNTDOWN:%s:%s:%s',
                $countdown['prayer_name'] ?? 'null',
                isset($countdown['prayer_time']) ? (string) $countdown['prayer_time'] : 'null',
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
