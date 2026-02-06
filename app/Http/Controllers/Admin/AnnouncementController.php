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
        return view('admin.announcements.form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:250',
            'active' => 'boolean',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'active' => $request->has('active'),
            'priority' => $request->priority ?? 1,
            'is_active' => $request->has('active'),
            'display_duration' => 10,
            'font_size' => 48,
            'text_color' => '#000000',
            'background_color' => '#ffffff',
            'scroll_speed' => 5,
        ]);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.form', compact('announcement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:250',
            'active' => 'boolean',
            'priority' => 'nullable|integer|between:1,3',
        ]);

        $announcement->update([
            'title' => $request->title,
            'content' => $request->content,
            'active' => $request->has('active'),
            'is_active' => $request->has('active'),
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
