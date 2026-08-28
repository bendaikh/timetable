<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Support\PrayerJamaatTime;
use App\Support\ScheduledMediaWindow;
use App\Support\MediaScheduleDuration;
use App\Support\ScheduleDaysOfWeek;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MediaScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = MediaSchedule::with('mediaItems')->orderBy('id', 'desc')->paginate(20);
        return view('admin.media-schedules.index', compact('schedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $media = Media::where('is_active', true)->get();
        // Don't show used priorities on initial load - they will be calculated dynamically
        // based on the actual schedule time the user selects
        $usedPriorities = [];
        
        return view('admin.media-schedules.create', compact('media', 'usedPriorities'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media_ids' => 'required|array|min:1',
            'media_ids.*' => 'required|exists:media,id',
            'schedule_type' => 'required|in:minutes_before_prayer,minutes_after_prayer,full_time_poster',
            'prayer_name' => 'required_unless:schedule_type,full_time_poster|nullable|in:fajr,zohar,asr,maghrib,isha',
            'minutes_before_prayer' => 'required_if:schedule_type,minutes_before_prayer|nullable|integer|min:1|max:480',
            'minutes_after_prayer' => 'required_if:schedule_type,minutes_after_prayer|nullable|integer|min:1|max:480',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'media_durations' => 'required|array',
            'media_durations.*' => 'required|numeric|min:' . MediaScheduleDuration::MIN_SECONDS . '|max:' . MediaScheduleDuration::MAX_SECONDS,
            'media_priorities' => 'required|array',
            'media_priorities.*' => 'required|integer|min:1|max:100',
            // Pivot table display window fields
            'media_start_dates' => 'nullable|array',
            'media_start_dates.*' => 'nullable|date',
            'media_start_times' => 'nullable|array',
            'media_start_times.*' => 'nullable|date_format:H:i',
            'media_expiry_dates' => 'nullable|array',
            'media_expiry_dates.*' => 'nullable|date',
            'media_expiry_times' => 'nullable|array',
            'media_expiry_times.*' => 'nullable|date_format:H:i',
            'media_gap_durations' => 'nullable|array',
            'media_gap_durations.*' => 'nullable|integer|min:0|max:3600',
            'media_days_of_week' => 'nullable|array',
            'media_days_of_week.*' => 'nullable|array'
        ]);

        ScheduledMediaWindow::validateRequestWindows(
            $request->media_ids,
            $request->media_start_dates,
            $request->media_start_times,
            $request->media_expiry_dates,
            $request->media_expiry_times,
            Setting::get('timezone', 'Europe/London')
        );

        ScheduledMediaWindow::validateRequestWindows(
            $request->media_ids,
            $request->media_start_dates,
            $request->media_start_times,
            $request->media_expiry_dates,
            $request->media_expiry_times,
            Setting::get('timezone', 'Europe/London')
        );

        $data = [
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->schedule_type === 'minutes_before_prayer') {
            $data['minutes_before_prayer'] = $request->minutes_before_prayer;
            $data['minutes_after_prayer'] = null;
        } elseif ($request->schedule_type === 'minutes_after_prayer') {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = $request->minutes_after_prayer;
        } else {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = null;
        }

        $pivotData = [];
        foreach ($request->media_ids as $index => $mediaId) {
            $mediaDays = ScheduleDaysOfWeek::normalizeFromRequest($request->media_days_of_week[$index] ?? null);
            $pivotData[$mediaId] = [
                'duration' => MediaScheduleDuration::secondsForStorage(
                    $request->media_durations[$index] ?? MediaScheduleDuration::DEFAULT_SECONDS
                ),
                'priority' => $request->media_priorities[$index] ?? ($index + 1),
                'start_date' => filled($request->media_start_dates[$index] ?? null) ? $request->media_start_dates[$index] : null,
                'start_time' => filled($request->media_start_times[$index] ?? null) ? $request->media_start_times[$index] : null,
                'expiry_date' => filled($request->media_expiry_dates[$index] ?? null) ? $request->media_expiry_dates[$index] : null,
                'expiry_time' => filled($request->media_expiry_times[$index] ?? null) ? $request->media_expiry_times[$index] : null,
                'gap_duration' => $request->media_gap_durations[$index] ?? 0,
                'days_of_week' => $mediaDays !== null ? json_encode($mediaDays) : null,
            ];
        }

        $scheduleDays = ScheduleDaysOfWeek::normalizeFromRequest($request->days_of_week);
        if ($scheduleDays === null) {
            $scheduleDays = $this->deriveScheduleDaysFromPivot($pivotData);
        }
        $data['days_of_week'] = $scheduleDays;

        $schedule = MediaSchedule::create($data);
        $schedule->mediaItems()->attach($pivotData);

        return redirect()->route('admin.media-schedules.index')
            ->with('success', 'Media schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MediaSchedule $mediaSchedule)
    {
        $mediaSchedule->load('mediaItems');
        return view('admin.media-schedules.show', compact('mediaSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MediaSchedule $mediaSchedule)
    {
        $media = Media::where('is_active', true)->get();
        // Don't show used priorities on initial load - they will be calculated dynamically
        // based on the actual schedule time the user selects
        $usedPriorities = [];
        
        return view('admin.media-schedules.edit', compact('mediaSchedule', 'media', 'usedPriorities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MediaSchedule $mediaSchedule)
    {
        $request->validate([
            'media_ids' => 'required|array|min:1',
            'media_ids.*' => 'required|exists:media,id',
            'schedule_type' => 'required|in:minutes_before_prayer,minutes_after_prayer,full_time_poster',
            'prayer_name' => 'required_unless:schedule_type,full_time_poster|nullable|in:fajr,zohar,asr,maghrib,isha',
            'minutes_before_prayer' => 'required_if:schedule_type,minutes_before_prayer|nullable|integer|min:1|max:480',
            'minutes_after_prayer' => 'required_if:schedule_type,minutes_after_prayer|nullable|integer|min:1|max:480',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'media_durations' => 'required|array',
            'media_durations.*' => 'required|numeric|min:' . MediaScheduleDuration::MIN_SECONDS . '|max:' . MediaScheduleDuration::MAX_SECONDS,
            'media_priorities' => 'required|array',
            'media_priorities.*' => 'required|integer|min:1|max:100',
            // Pivot table display window fields
            'media_start_dates' => 'nullable|array',
            'media_start_dates.*' => 'nullable|date',
            'media_start_times' => 'nullable|array',
            'media_start_times.*' => 'nullable|date_format:H:i',
            'media_expiry_dates' => 'nullable|array',
            'media_expiry_dates.*' => 'nullable|date',
            'media_expiry_times' => 'nullable|array',
            'media_expiry_times.*' => 'nullable|date_format:H:i',
            'media_gap_durations' => 'nullable|array',
            'media_gap_durations.*' => 'nullable|integer|min:0|max:3600',
            'media_days_of_week' => 'nullable|array',
            'media_days_of_week.*' => 'nullable|array'
        ]);

        ScheduledMediaWindow::validateRequestWindows(
            $request->media_ids,
            $request->media_start_dates,
            $request->media_start_times,
            $request->media_expiry_dates,
            $request->media_expiry_times,
            Setting::get('timezone', 'Europe/London')
        );

        $data = [
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->schedule_type === 'minutes_before_prayer') {
            $data['minutes_before_prayer'] = $request->minutes_before_prayer;
            $data['minutes_after_prayer'] = null;
        } elseif ($request->schedule_type === 'minutes_after_prayer') {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = $request->minutes_after_prayer;
        } else {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = null;
        }

        $pivotData = [];
        foreach ($request->media_ids as $index => $mediaId) {
            $mediaDays = ScheduleDaysOfWeek::normalizeFromRequest($request->media_days_of_week[$index] ?? null);
            $pivotData[$mediaId] = [
                'duration' => MediaScheduleDuration::secondsForStorage(
                    $request->media_durations[$index] ?? MediaScheduleDuration::DEFAULT_SECONDS
                ),
                'priority' => $request->media_priorities[$index] ?? ($index + 1),
                'start_date' => filled($request->media_start_dates[$index] ?? null) ? $request->media_start_dates[$index] : null,
                'start_time' => filled($request->media_start_times[$index] ?? null) ? $request->media_start_times[$index] : null,
                'expiry_date' => filled($request->media_expiry_dates[$index] ?? null) ? $request->media_expiry_dates[$index] : null,
                'expiry_time' => filled($request->media_expiry_times[$index] ?? null) ? $request->media_expiry_times[$index] : null,
                'gap_duration' => $request->media_gap_durations[$index] ?? 0,
                'days_of_week' => $mediaDays !== null ? json_encode($mediaDays) : null,
            ];
        }

        $scheduleDays = ScheduleDaysOfWeek::normalizeFromRequest($request->days_of_week);
        if ($scheduleDays === null) {
            $scheduleDays = $this->deriveScheduleDaysFromPivot($pivotData);
        }
        $data['days_of_week'] = $scheduleDays;

        $mediaSchedule->update($data);
        $mediaSchedule->mediaItems()->sync($pivotData);

        return redirect()->route('admin.media-schedules.index')
            ->with('success', 'Media schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaSchedule $mediaSchedule)
    {
        $mediaSchedule->delete();

        return redirect()->route('admin.media-schedules.index')
            ->with('success', 'Media schedule deleted successfully.');
    }

    /**
     * Toggle schedule active status
     */
    public function toggleStatus(MediaSchedule $mediaSchedule)
    {
        $mediaSchedule->update(['is_active' => !$mediaSchedule->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $mediaSchedule->is_active
        ]);
    }

    /**
     * Check for overlapping schedules and get available priorities
     */
    public function checkOverlap(Request $request)
    {
        $scheduleType = $request->schedule_type;
        $prayerName = $request->prayer_name;
        $minutes = $request->minutes;
        $mediaId = $request->media_id;
        $excludeId = $request->exclude_id; // When editing, exclude the current schedule
        
        // Get today's prayer times
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        
        if (!$prayerTimes || !$prayerName) {
            return response()->json([
                'success' => false,
                'message' => 'Prayer times not available for today'
            ]);
        }
        
        // Get media duration if media_id is provided
        $mediaDuration = 30; // Default 30 seconds
        if ($mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                $mediaDuration = (int) $media->display_duration;
            }
        }
        
        // Calculate the display start and end time based on JAMAAT TIME (not beginning time)
        $jamaatTime = PrayerJamaatTime::resolve($prayerTimes, $prayerName);
        if (!$jamaatTime) {
            return response()->json([
                'success' => false,
                'message' => 'Jamaat time not available for ' . $prayerName,
            ]);
        }
        
        if ($scheduleType === 'minutes_before_prayer') {
            $displayStart = $jamaatTime->copy()->subMinutes((int) $minutes);
            $displayEnd = $jamaatTime->copy(); // until jamaat (same rule as PrayerJamaatTime)
        } else { // minutes_after_prayer
            $displayStart = $jamaatTime->copy()->addMinutes((int) $minutes);
            $displayEnd = $displayStart->copy()->addMinutes(\App\Support\PrayerJamaatTime::AFTER_POSTER_WINDOW_MINUTES);
        }
        
        // Find all active schedules
        $query = MediaSchedule::with('mediaItems')
            ->where('is_active', true);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $allSchedules = $query->get();
        
        // Filter schedules that overlap with the calculated time
        $overlappingSchedules = [];
        $usedPrioritiesInOverlap = [];
        foreach ($allSchedules as $schedule) {
            $scheduleStart = $schedule->getDisplayStartTime();
            $scheduleEnd = $schedule->getDisplayEndTime();
            
            if ($scheduleStart && $scheduleEnd) {
                // Check if time ranges overlap
                if ($scheduleStart->lt($displayEnd) && $scheduleEnd->gt($displayStart)) {
                    // Get media names
                    $mediaNames = $schedule->mediaItems->pluck('name')->join(', ') ?: 'No media';
                    
                    // Get minimum priority from pivot table
                    $minPriority = $schedule->mediaItems->min('pivot.priority') ?: 1;
                    
                    $overlappingSchedules[] = [
                        'id' => $schedule->id,
                        'media_name' => $mediaNames,
                        'priority' => $minPriority,
                        'schedule_type' => $schedule->getScheduleTypeLabel(),
                        'prayer_name' => $schedule->getPrayerNameLabel(),
                        'start_time' => $scheduleStart->format('h:i A'),
                        'end_time' => $scheduleEnd->format('h:i A'),
                    ];
                    $usedPrioritiesInOverlap[] = $minPriority;
                }
            }
        }
        
        // Sort used priorities
        sort($usedPrioritiesInOverlap);
        
        // Suggest next available priority (only considering overlapping schedules)
        $suggestedPriority = 1;
        while (in_array($suggestedPriority, $usedPrioritiesInOverlap)) {
            $suggestedPriority++;
        }
        
        // Get available priorities (1-100) - excluding those used in overlapping schedules
        $availablePriorities = [];
        for ($i = 1; $i <= 100; $i++) {
            if (!in_array($i, $usedPrioritiesInOverlap)) {
                $availablePriorities[] = $i;
            }
        }
        
        return response()->json([
            'success' => true,
            'jamaat_time' => $jamaatTime->format('h:i A'),
            'display_start' => $displayStart->format('h:i A'),
            'display_end' => $displayEnd->format('h:i A'),
            'display_start_iso' => $displayStart->toIso8601String(),
            'display_end_iso' => $displayEnd->toIso8601String(),
            'jamaat_time_iso' => $jamaatTime->toIso8601String(),
            'overlapping_schedules' => $overlappingSchedules,
            'suggested_priority' => $suggestedPriority,
            'available_priorities' => $availablePriorities,
            'used_priorities' => $usedPrioritiesInOverlap
        ]);
    }

    /**
     * When schedule-level days are not set, copy from per-media day limits (single poster use case).
     *
     * @param  array<int|string, array<string, mixed>>  $pivotData
     * @return list<int>|null
     */
    private function deriveScheduleDaysFromPivot(array $pivotData): ?array
    {
        $combined = [];

        foreach ($pivotData as $row) {
            $days = ScheduleDaysOfWeek::normalize($row['days_of_week'] ?? null);
            if ($days !== null) {
                $combined = array_merge($combined, $days);
            }
        }

        return ScheduleDaysOfWeek::normalize($combined);
    }
}
