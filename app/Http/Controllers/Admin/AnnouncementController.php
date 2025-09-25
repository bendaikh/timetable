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
            'is_active' => 'boolean',
            'auto_repeat' => 'boolean',
            'repeat_days' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'display_duration' => 'required|integer|min:1|max:120',
            'font_size' => 'required|integer|min:12|max:72',
            'text_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'scroll_speed' => 'required|integer|min:1|max:10',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['auto_repeat'] = $request->has('auto_repeat');

        Announcement::create($data);

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
            'is_active' => 'boolean',
            'auto_repeat' => 'boolean',
            'repeat_days' => 'nullable|array',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'display_duration' => 'required|integer|min:1|max:120',
            'font_size' => 'required|integer|min:12|max:72',
            'text_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'scroll_speed' => 'required|integer|min:1|max:10',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');
        $data['auto_repeat'] = $request->has('auto_repeat');

        $announcement->update($data);

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
