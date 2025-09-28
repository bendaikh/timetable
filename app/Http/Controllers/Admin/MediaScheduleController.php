<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\MediaSchedule;
use Illuminate\Http\Request;

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
        return view('admin.media-schedules.create', compact('media'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'media_id' => 'required|exists:media,id',
            'schedule_type' => 'required|in:prayer_before,prayer_after,time_range,countdown',
            'prayer_name' => 'required_if:schedule_type,prayer_before,prayer_after|in:fajr,zohar,asr,maghrib,isha',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'start_time' => 'required_if:schedule_type,time_range|nullable|date_format:H:i',
            'end_time' => 'required_if:schedule_type,time_range|nullable|date_format:H:i|after:start_time',
            'countdown_duration' => 'required_if:schedule_type,countdown|integer|min:10|max:300',
            'priority' => 'required|integer|min:0|max:100'
        ]);

        MediaSchedule::create([
            'media_id' => $request->media_id,
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'countdown_duration' => $request->countdown_duration ?? 30,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active')
        ]);

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
        return view('admin.media-schedules.edit', compact('mediaSchedule', 'media'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MediaSchedule $mediaSchedule)
    {
        $request->validate([
            'media_id' => 'required|exists:media,id',
            'schedule_type' => 'required|in:prayer_before,prayer_after,time_range,countdown',
            'prayer_name' => 'required_if:schedule_type,prayer_before,prayer_after|in:fajr,zohar,asr,maghrib,isha',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'start_time' => 'required_if:schedule_type,time_range|nullable|date_format:H:i',
            'end_time' => 'required_if:schedule_type,time_range|nullable|date_format:H:i|after:start_time',
            'countdown_duration' => 'required_if:schedule_type,countdown|integer|min:10|max:300',
            'priority' => 'required|integer|min:0|max:100'
        ]);

        $mediaSchedule->update([
            'media_id' => $request->media_id,
            'schedule_type' => $request->schedule_type,
            'prayer_name' => $request->prayer_name,
            'days_of_week' => $request->days_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'countdown_duration' => $request->countdown_duration ?? 30,
            'priority' => $request->priority,
            'is_active' => $request->has('is_active')
        ]);

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
}
