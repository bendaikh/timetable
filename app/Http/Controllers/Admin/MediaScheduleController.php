<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MediaScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $schedules = MediaSchedule::with('media')->orderBy('priority', 'desc')->paginate(20);
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
            'media_id' => 'required|exists:media,id',
            'schedule_type' => 'required|in:minutes_before_prayer,minutes_after_prayer',
            'prayer_name' => 'required|in:fajr,zohar,asr,maghrib,isha',
            'minutes_before_prayer' => 'nullable|integer|min:5|max:120',
            'minutes_after_prayer' => 'nullable|integer|min:1|max:120',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'priority' => 'required|integer|min:1|max:100'
        ]);

        // Check if priority conflicts with overlapping schedules
        $conflictingSchedule = $this->checkPriorityConflict(
            $request->schedule_type,
            $request->prayer_name,
            $request->schedule_type === 'minutes_before_prayer' ? $request->minutes_before_prayer : $request->minutes_after_prayer,
            $request->priority,
            null,
            $request->media_id
        );

        if ($conflictingSchedule) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['priority' => "Priority {$request->priority} is already used by \"{$conflictingSchedule->media->title}\" which displays at the same time. Please choose a different priority."]);
        }

        $data = [
            'media_id' => $request->media_id,
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active')
        ];

        if ($request->schedule_type === 'minutes_before_prayer') {
            $data['minutes_before_prayer'] = $request->minutes_before_prayer;
            $data['minutes_after_prayer'] = null;
        } else {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = $request->minutes_after_prayer;
        }

        MediaSchedule::create($data);

        return redirect()->route('admin.media-schedules.index')
            ->with('success', 'Media schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MediaSchedule $mediaSchedule)
    {
        $mediaSchedule->load('media');
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
            'media_id' => 'required|exists:media,id',
            'schedule_type' => 'required|in:minutes_before_prayer,minutes_after_prayer',
            'prayer_name' => 'required|in:fajr,zohar,asr,maghrib,isha',
            'minutes_before_prayer' => 'nullable|integer|min:5|max:120',
            'minutes_after_prayer' => 'nullable|integer|min:1|max:120',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'priority' => 'required|integer|min:1|max:100'
        ]);

        // Check if priority conflicts with overlapping schedules (excluding current schedule)
        $conflictingSchedule = $this->checkPriorityConflict(
            $request->schedule_type,
            $request->prayer_name,
            $request->schedule_type === 'minutes_before_prayer' ? $request->minutes_before_prayer : $request->minutes_after_prayer,
            $request->priority,
            $mediaSchedule->id,
            $request->media_id
        );

        if ($conflictingSchedule) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['priority' => "Priority {$request->priority} is already used by \"{$conflictingSchedule->media->title}\" which displays at the same time. Please choose a different priority."]);
        }

        $data = [
            'media_id' => $request->media_id,
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active')
        ];

        if ($request->schedule_type === 'minutes_before_prayer') {
            $data['minutes_before_prayer'] = $request->minutes_before_prayer;
            $data['minutes_after_prayer'] = null;
        } else {
            $data['minutes_before_prayer'] = null;
            $data['minutes_after_prayer'] = $request->minutes_after_prayer;
        }

        $mediaSchedule->update($data);

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
        
        // Calculate the display start and end time
        $prayerTime = Carbon::parse($prayerTimes->$prayerName);
        
        if ($scheduleType === 'minutes_before_prayer') {
            $displayStart = $prayerTime->copy()->subMinutes((int) $minutes);
            $displayEnd = $prayerTime->copy()->subMinutes(5); // Stops 5 minutes before prayer
        } else { // minutes_after_prayer
            $displayStart = $prayerTime->copy()->addMinutes((int) $minutes);
            $displayEnd = $displayStart->copy()->addSeconds($mediaDuration); // Use actual media duration
        }
        
        // Find all active schedules
        $query = MediaSchedule::with('media')
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
                    $overlappingSchedules[] = [
                        'id' => $schedule->id,
                        'media_name' => $schedule->media->name,
                        'priority' => $schedule->priority,
                        'schedule_type' => $schedule->getScheduleTypeLabel(),
                        'prayer_name' => $schedule->getPrayerNameLabel(),
                        'start_time' => $scheduleStart->format('h:i A'),
                        'end_time' => $scheduleEnd->format('h:i A'),
                    ];
                    $usedPrioritiesInOverlap[] = $schedule->priority;
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

    /**
     * Check if priority conflicts with any overlapping schedules
     * 
     * @param string $scheduleType
     * @param string $prayerName
     * @param int $minutes
     * @param int $priority
     * @param int|null $excludeId
     * @param int|null $mediaId
     * @return MediaSchedule|null
     */
    private function checkPriorityConflict($scheduleType, $prayerName, $minutes, $priority, $excludeId = null, $mediaId = null)
    {
        // Get today's prayer times
        $prayerTimes = PrayerTime::getTodayPrayerTimes();
        
        if (!$prayerTimes || !$prayerName) {
            return null;
        }
        
        // Get media duration if media_id is provided
        $mediaDuration = 30; // Default 30 seconds
        if ($mediaId) {
            $media = Media::find($mediaId);
            if ($media) {
                $mediaDuration = (int) $media->display_duration;
            }
        }
        
        // Calculate the display start and end time for the new/edited schedule
        $prayerTime = Carbon::parse($prayerTimes->$prayerName);
        
        if ($scheduleType === 'minutes_before_prayer') {
            $displayStart = $prayerTime->copy()->subMinutes((int) $minutes);
            $displayEnd = $prayerTime->copy()->subMinutes(5);
        } else {
            $displayStart = $prayerTime->copy()->addMinutes((int) $minutes);
            $displayEnd = $displayStart->copy()->addSeconds($mediaDuration);
        }
        
        // Find all active schedules with the same priority
        $query = MediaSchedule::with('media')
            ->where('is_active', true)
            ->where('priority', $priority);
            
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $schedulesWithSamePriority = $query->get();
        
        // Check if any of them overlap with our time range
        foreach ($schedulesWithSamePriority as $schedule) {
            $scheduleStart = $schedule->getDisplayStartTime();
            $scheduleEnd = $schedule->getDisplayEndTime();
            
            if ($scheduleStart && $scheduleEnd) {
                // Check if time ranges overlap
                if ($scheduleStart->lt($displayEnd) && $scheduleEnd->gt($displayStart)) {
                    return $schedule; // Found a conflict
                }
            }
        }
        
        return null; // No conflict
    }
}
