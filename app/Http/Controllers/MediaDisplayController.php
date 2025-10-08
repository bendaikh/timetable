<?php

namespace App\Http\Controllers;

use App\Services\MediaDisplayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MediaDisplayController extends Controller
{
    protected MediaDisplayService $mediaDisplayService;

    public function __construct(MediaDisplayService $mediaDisplayService)
    {
        $this->mediaDisplayService = $mediaDisplayService;
    }

    /**
     * Get current media to display
     */
    public function getCurrentMedia(): JsonResponse
    {
        if (!$this->mediaDisplayService->isMediaDisplayEnabled()) {
            return response()->json(['media' => null]);
        }

        $mediaInfo = $this->mediaDisplayService->getCurrentMedia();
        
        if (!$mediaInfo) {
            return response()->json(['media' => null]);
        }

        $media = $mediaInfo['media'];
        $duration = $mediaInfo['duration'];

        return response()->json([
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
        ]);
    }

    /**
     * Get countdown information
     */
    public function getCountdownInfo(): JsonResponse
    {
        $countdownInfo = $this->mediaDisplayService->getCountdownInfo();
        
        return response()->json([
            'countdown' => $countdownInfo
        ]);
    }

    /**
     * Get media display status
     */
    public function getStatus(): JsonResponse
    {
        return response()->json([
            'enabled' => $this->mediaDisplayService->isMediaDisplayEnabled(),
            'current_media' => $this->mediaDisplayService->getCurrentMedia(),
            'countdown_info' => $this->mediaDisplayService->getCountdownInfo()
        ]);
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
}
