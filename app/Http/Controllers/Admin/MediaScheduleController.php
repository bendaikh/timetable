<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
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
            'media_durations.*' => 'required|numeric|min:0.5',
            'media_priorities' => 'required|array',
            'media_priorities.*' => 'required|integer|min:1|max:100',
            // New fields for pivot table
            'media_expiry_dates' => 'nullable|array',
            'media_expiry_dates.*' => 'nullable|date',
            'media_expiry_times' => 'nullable|array',
            'media_expiry_times.*' => 'nullable|date_format:H:i',
            'media_gap_durations' => 'nullable|array',
            'media_gap_durations.*' => 'nullable|integer|min:0|max:3600',
            'media_days_of_week' => 'nullable|array',
            'media_days_of_week.*' => 'nullable|array'
        ]);

        $data = [
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'is_active' => $request->has('is_active')
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

        $schedule = MediaSchedule::create($data);

        // Attach media items with their duration, priority, and new fields
        $pivotData = [];
        foreach ($request->media_ids as $index => $mediaId) {
            $pivotData[$mediaId] = [
                'duration' => $request->media_durations[$index] ?? 30,
                'priority' => $request->media_priorities[$index] ?? ($index + 1),
                'expiry_date' => $request->media_expiry_dates[$index] ?? null,
                'expiry_time' => $request->media_expiry_times[$index] ?? null,
                'gap_duration' => $request->media_gap_durations[$index] ?? 0,
                'days_of_week' => isset($request->media_days_of_week[$index]) && !empty($request->media_days_of_week[$index])
                    ? json_encode($request->media_days_of_week[$index]) 
                    : null
            ];
        }
        
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
            'media_durations.*' => 'required|numeric|min:0.5',
            'media_priorities' => 'required|array',
            'media_priorities.*' => 'required|integer|min:1|max:100',
            // New fields for pivot table
            'media_expiry_dates' => 'nullable|array',
            'media_expiry_dates.*' => 'nullable|date',
            'media_expiry_times' => 'nullable|array',
            'media_expiry_times.*' => 'nullable|date_format:H:i',
            'media_gap_durations' => 'nullable|array',
            'media_gap_durations.*' => 'nullable|integer|min:0|max:3600',
            'media_days_of_week' => 'nullable|array',
            'media_days_of_week.*' => 'nullable|array'
        ]);

        $data = [
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'is_active' => $request->has('is_active')
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

        $mediaSchedule->update($data);

        // Sync media items with their duration, priority, and new fields
        $pivotData = [];
        foreach ($request->media_ids as $index => $mediaId) {
            $pivotData[$mediaId] = [
                'duration' => $request->media_durations[$index] ?? 30,
                'priority' => $request->media_priorities[$index] ?? ($index + 1),
                'expiry_date' => $request->media_expiry_dates[$index] ?? null,
                'expiry_time' => $request->media_expiry_times[$index] ?? null,
                'gap_duration' => $request->media_gap_durations[$index] ?? 0,
                'days_of_week' => isset($request->media_days_of_week[$index]) && !empty($request->media_days_of_week[$index])
                    ? json_encode($request->media_days_of_week[$index]) 
                    : null
            ];
        }
        
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
        $beginningTime = Carbon::parse($prayerTimes->$prayerName);
        
        // Get Jamaat offset from settings and add to beginning time to get Jamaat time
        $jamaatOffset = (int) Setting::get($prayerName . '_jamaat_offset', 0);
        $jamaatTime = $beginningTime->copy()->addMinutes($jamaatOffset);
        
        if ($scheduleType === 'minutes_before_prayer') {
            $displayStart = $jamaatTime->copy()->subMinutes((int) $minutes);
            $displayEnd = $jamaatTime->copy()->subMinutes(5); // Stops 5 minutes before Jamaat
        } else { // minutes_after_prayer
            $displayStart = $jamaatTime->copy()->addMinutes((int) $minutes);
            $displayEnd = $displayStart->copy()->addSeconds($mediaDuration); // Use actual media duration
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
            'display_start' => $displayStart->format('h:i A'),
            'display_end' => $displayEnd->format('h:i A'),
            'overlapping_schedules' => $overlappingSchedules,
            'suggested_priority' => $suggestedPriority,
            'available_priorities' => $availablePriorities,
            'used_priorities' => $usedPrioritiesInOverlap
        ]);
    }

}
