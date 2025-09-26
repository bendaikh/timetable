<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $settings = Setting::orderBy('key')->paginate(15);
        $defaultSettings = Setting::getDefaults();
        return view('admin.settings.index', compact('settings', 'defaultSettings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.settings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key',
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        Setting::create($request->all());

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Setting $setting)
    {
        return view('admin.settings.show', compact('setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Setting $setting)
    {
        return view('admin.settings.edit', compact('setting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Setting $setting)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,json',
            'description' => 'nullable|string',
        ]);

        $setting->update($request->all());

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Setting $setting)
    {
        $setting->delete();

        return redirect()->route('admin.settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Update multiple settings at once
     */
    public function updateBatch(Request $request)
    {
        try {
            \Log::info('Settings updateBatch called', [
                'request_data' => $request->all(),
                'has_file' => $request->hasFile('logo')
            ]);
            
            $settings = $request->input('settings', []);
            
            // Handle logo upload
            if ($request->hasFile('logo')) {
                $request->validate([
                    'logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
                ]);
                
                $logo = $request->file('logo');
                $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                
                \Log::info('Attempting to store logo', [
                    'original_name' => $logo->getClientOriginalName(),
                    'size' => $logo->getSize(),
                    'mime_type' => $logo->getMimeType(),
                    'target_name' => $logoName
                ]);
                
                // Store the file in public disk
                $logoPath = $logo->storeAs('logos', $logoName, 'public');
                
                \Log::info('Logo stored successfully', [
                    'path' => $logoPath,
                    'full_path' => storage_path('app/public/' . $logoPath)
                ]);
                
                $settings['logo_path'] = $logoPath;
            }
            
            \Log::info('Settings to update', ['settings' => $settings]);
            
            foreach ($settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value, 'type' => 'string']
                );
            }

            \Log::info('Settings updated successfully');
            return redirect()->route('admin.settings.index')
                ->with('success', 'Settings updated successfully.');
                
        } catch (\Exception $e) {
            \Log::error('Settings update failed: ' . $e->getMessage());
            return redirect()->route('admin.settings.index')
                ->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}
