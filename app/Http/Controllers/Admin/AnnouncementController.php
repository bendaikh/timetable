<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->paginate(15);
        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.announcements.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'title_font_size' => 'required|integer|min:20|max:60',
            'display_duration' => 'required|integer|min:1|max:120',
            'font_size' => 'required|integer|min:12|max:160',
            'scroll_speed' => 'required|integer|min:1|max:10',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'start_date' => 'nullable|date_format:Y-m-d\TH:i',
            'end_date' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_repeat' => 'boolean',
            'repeat_days' => 'nullable|array',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'title_font_size' => $request->title_font_size,
            'display_duration' => $request->display_duration,
            'font_size' => $request->font_size,
            'scroll_speed' => $request->scroll_speed,
            'text_color' => $request->text_color,
            'background_color' => $request->background_color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->has('is_active'),
            'auto_repeat' => $request->has('auto_repeat'),
            'repeat_days' => $request->has('repeat_days') ? $request->repeat_days : null,
            'priority' => $request->priority ?? 1,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement)
    {
        return view('admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'display_duration' => 'required|integer|min:1|max:120',
            'font_size' => 'required|integer|min:12|max:160',
            'scroll_speed' => 'required|integer|min:1|max:10',
            'text_color' => 'required|string',
            'background_color' => 'required|string',
            'start_date' => 'nullable|date_format:Y-m-d\TH:i',
            'end_date' => 'nullable|date_format:Y-m-d\TH:i|after_or_equal:start_date',
            'is_active' => 'boolean',
            'auto_repeat' => 'boolean',
            'repeat_days' => 'nullable|array',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'display_duration' => $request->display_duration,
            'font_size' => $request->font_size,
            'scroll_speed' => $request->scroll_speed,
            'text_color' => $request->text_color,
            'background_color' => $request->background_color,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'is_active' => $request->has('is_active'),
            'auto_repeat' => $request->has('auto_repeat'),
            'repeat_days' => $request->has('repeat_days') ? $request->repeat_days : null,
            'priority' => $request->priority ?? 1,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
