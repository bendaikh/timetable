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

        $media = $this->mediaDisplayService->getCurrentMedia();
        
        if (!$media) {
            return response()->json(['media' => null]);
        }

        return response()->json([
            'media' => [
                'id' => $media->id,
                'title' => $media->title,
                'type' => $media->type,
                'file_url' => $media->file_url,
                'display_duration' => $media->display_duration,
                'description' => $media->description
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
            'active_schedules' => $this->mediaDisplayService->getActiveSchedules()
        ]);
    }
}
