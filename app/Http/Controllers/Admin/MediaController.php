<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Convert PHP size string (e.g., '2M', '1G') to bytes
     */
    private function convertToBytes($value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value)-1]);
        $value = (int)$value;

        switch($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $media = Media::with('schedules')->orderBy('id', 'desc')->paginate(20);
        return view('admin.media.index', compact('media'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.media.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Get PHP's upload limit
        $maxUploadSize = min(
            $this->convertToBytes(ini_get('upload_max_filesize')),
            $this->convertToBytes(ini_get('post_max_size'))
        );
        $maxUploadSizeKB = intval($maxUploadSize / 1024);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => "required|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov|max:$maxUploadSizeKB",
            'description' => 'nullable|string',
            'type' => 'required|in:image,video'
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('media', $fileName, 'public');

        $media = Media::create([
            'title' => $request->title,
            'file_path' => $filePath,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Media $medium)
    {
        $medium->load('schedules');
        return view('admin.media.show', compact('medium'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Media $medium)
    {
        return view('admin.media.edit', compact('medium'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Media $medium)
    {
        // Get PHP's upload limit
        $maxUploadSize = min(
            $this->convertToBytes(ini_get('upload_max_filesize')),
            $this->convertToBytes(ini_get('post_max_size'))
        );
        $maxUploadSizeKB = intval($maxUploadSize / 1024);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => "nullable|file|mimes:jpg,jpeg,png,gif,mp4,avi,mov|max:$maxUploadSizeKB",
            'description' => 'nullable|string',
            'type' => 'required|in:image,video'
        ]);

        $data = [
            'title' => $request->title,
            'type' => $request->type,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ];

        if ($request->hasFile('file')) {
            // Delete old file
            if ($medium->file_path) {
                Storage::disk('public')->delete($medium->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . Str::slug($request->title) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('media', $fileName, 'public');
            $data['file_path'] = $filePath;
        }

        $medium->update($data);

        return redirect()->route('admin.media.index')
            ->with('success', 'Media updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Media $medium)
    {
        // Delete file from storage
        if ($medium->file_path) {
            Storage::disk('public')->delete($medium->file_path);
        }

        $medium->delete();

        return redirect()->route('admin.media.index')
            ->with('success', 'Media deleted successfully.');
    }

    /**
     * Preview media as it would appear on timetable
     */
    public function preview(Media $medium)
    {
        return view('admin.media.preview', compact('medium'));
    }

    /**
     * Toggle media active status
     */
    public function toggleStatus(Media $media)
    {
        $media->update(['is_active' => !$media->is_active]);
        
        return response()->json([
            'success' => true,
            'is_active' => $media->is_active
        ]);
    }
}
