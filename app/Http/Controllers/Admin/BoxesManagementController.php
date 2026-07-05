<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoxSetting;
use App\Models\Setting;
use App\Support\CssUnits;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BoxesManagementController extends Controller
{
    private function normalizeSettingArray($value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function normalizeAnnouncementsStylingSettings(array $stylingSettings): array
    {
        if (isset($stylingSettings['title_font_size'])) {
            $stylingSettings['title_font_size'] = CssUnits::normalizeBoxRem(
                $stylingSettings['title_font_size'],
                '1.2rem'
            );
        }

        return $stylingSettings;
    }

    /**
     * Display the boxes management dashboard
     */
    public function index()
    {
        // Initialize defaults only if no boxes exist at all
        if (BoxSetting::count() === 0) {
            BoxSetting::initializeDefaults();
        }
        
        $boxes = BoxSetting::orderBy('sort_order')
            ->whereNotIn('box_type', ['welcome_box', 'note_prayer_box', 'hadeeth_box'])
            ->get();
        $defaultBoxes = BoxSetting::getDefaultBoxSettings();
        
        return view('admin.boxes.index', compact('boxes', 'defaultBoxes'));
    }

    /**
     * Show the form for editing a specific box
     */
    public function edit($boxType)
    {
        if (in_array($boxType, ['welcome_box', 'note_prayer_box'])) {
            return redirect()->route('admin.boxes.index')->with('error', 'This box is not editable.');
        }
        $box = BoxSetting::where('box_type', $boxType)->first();
        
        if (!$box) {
            // Create box from defaults if it doesn't exist
            $defaults = BoxSetting::getDefaultBoxSettings();
            if (isset($defaults[$boxType])) {
                $defaultBox = $defaults[$boxType];
                $box = BoxSetting::create([
                    'box_type' => $boxType,
                    'box_name' => $defaultBox['box_name'],
                    'content_settings' => $defaultBox['content_settings'],
                    'styling_settings' => $defaultBox['styling_settings'],
                    'layout_settings' => $defaultBox['layout_settings'],
                    'is_active' => true,
                    'sort_order' => array_search($boxType, array_keys($defaults))
                ]);
            } else {
                return redirect()->route('admin.boxes.index')->with('error', 'Invalid box type.');
            }
        }
        
        $defaultBoxes = BoxSetting::getDefaultBoxSettings();
        $defaultBox = $defaultBoxes[$boxType] ?? [];
        
        return view('admin.boxes.edit', compact('box', 'defaultBox', 'boxType'));
    }

    /**
     * Update a box's settings
     */
    public function update(Request $request, $boxType)
    {
        if (in_array($boxType, ['welcome_box', 'note_prayer_box'])) {
            return redirect()->route('admin.boxes.index')->with('error', 'This box is not editable.');
        }
        $box = BoxSetting::where('box_type', $boxType)->first();
        
        if (!$box) {
            return redirect()->route('admin.boxes.index')->with('error', 'Box not found.');
        }

        $validator = Validator::make($request->all(), [
            'box_name' => 'required|string|max:255',
            'content_settings' => 'nullable|array',
            'styling_settings' => 'nullable|array',
            'layout_settings' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Merge styling settings with existing ones (don't replace them)
        // Ensure existing settings are arrays, not strings
        $existingStyleSettings = $this->normalizeSettingArray($box->styling_settings);
        $requestStyleSettings = $this->normalizeSettingArray($request->styling_settings);
        
        $stylingSettings = array_merge(
            $existingStyleSettings,
            $requestStyleSettings
        );

        if ($box->box_type === 'announcements_box') {
            $stylingSettings = $this->normalizeAnnouncementsStylingSettings($stylingSettings);
        }
        
        // Convert hex background color to RGBA for storage
        if (isset($stylingSettings['background_color']) && strpos($stylingSettings['background_color'], '#') === 0) {
            $hex = $stylingSettings['background_color'];
            $r = hexdec(substr($hex, 1, 2));
            $g = hexdec(substr($hex, 3, 2));
            $b = hexdec(substr($hex, 5, 2));
            
            // For timetable background box, keep as hex for full opacity
            if ($box->box_type === 'timetable_background_box') {
                $stylingSettings['background_color'] = $hex;
            } else {
                $stylingSettings['background_color'] = $box->box_type === 'header_box' 
                    ? "rgba($r, $g, $b, 0.95)" 
                    : "rgba($r, $g, $b, 0.9)";
            }
        }

        // Merge content settings with existing ones
        // Ensure existing settings are arrays, not strings
        $existingContentSettings = $this->normalizeSettingArray($box->content_settings);
        $requestContentSettings = $this->normalizeSettingArray($request->content_settings);
        
        $contentSettings = array_merge(
            $existingContentSettings,
            $requestContentSettings
        );

        // Merge layout settings with existing ones
        // Ensure existing settings are arrays, not strings
        $existingLayoutSettings = $this->normalizeSettingArray($box->layout_settings);
        $requestLayoutSettings = $this->normalizeSettingArray($request->layout_settings);
        
        $layoutSettings = array_merge(
            $existingLayoutSettings,
            $requestLayoutSettings
        );

        $box->update([
            'box_name' => $request->box_name,
            'content_settings' => $contentSettings,
            'styling_settings' => $stylingSettings,
            'layout_settings' => $layoutSettings,
            'is_active' => $request->input('is_active', 0) == 1
        ]);

        // Ensure boxes-based styling is enabled so changes are visible on the timetable preview
        Setting::set('use_boxes_styling', 'enabled', 'string', 'Use boxes-based styling (enabled|disabled)');

        return redirect()->route('admin.boxes.index')
            ->with('success', 'Box settings updated successfully.');
    }

    /**
     * Update box settings via AJAX
     */
    public function updateAjax(Request $request, $boxType)
    {
        if (in_array($boxType, ['welcome_box', 'note_prayer_box'])) {
            return response()->json(['error' => 'This box is not editable.'], 403);
        }
        $box = BoxSetting::where('box_type', $boxType)->first();
        
        if (!$box) {
            return response()->json(['error' => 'Box not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'box_name' => 'sometimes|required|string|max:255',
            'content_settings' => 'sometimes|array',
            'styling_settings' => 'sometimes|array',
            'layout_settings' => 'sometimes|array',
            'is_active' => 'sometimes|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $updateData = [];
            
            if ($request->has('box_name')) {
                $updateData['box_name'] = $request->box_name;
            }
            
            if ($request->has('content_settings')) {
                $existingContentSettings = $this->normalizeSettingArray($box->content_settings);
                $requestContentSettings = $this->normalizeSettingArray($request->content_settings);
                $updateData['content_settings'] = array_merge($existingContentSettings, $requestContentSettings);
            }
            
            if ($request->has('styling_settings')) {
                $existingStyleSettings = $this->normalizeSettingArray($box->styling_settings);
                $requestStyleSettings = $this->normalizeSettingArray($request->styling_settings);
                $stylingSettings = array_merge($existingStyleSettings, $requestStyleSettings);

                if ($box->box_type === 'announcements_box') {
                    $stylingSettings = $this->normalizeAnnouncementsStylingSettings($stylingSettings);
                }

            // Convert hex background color to RGBA for storage
                if (isset($stylingSettings['background_color']) && strpos($stylingSettings['background_color'], '#') === 0) {
                    $hex = $stylingSettings['background_color'];
                    $r = hexdec(substr($hex, 1, 2));
                    $g = hexdec(substr($hex, 3, 2));
                    $b = hexdec(substr($hex, 5, 2));
                    
                    // For timetable background box, keep as hex for full opacity
                    if ($box->box_type === 'timetable_background_box') {
                        $stylingSettings['background_color'] = $hex;
                    } else {
                        $stylingSettings['background_color'] = $box->box_type === 'header_box' 
                            ? "rgba($r, $g, $b, 0.95)" 
                            : "rgba($r, $g, $b, 0.9)";
                    }
                }
                $updateData['styling_settings'] = $stylingSettings;
            }
            
            if ($request->has('layout_settings')) {
                $existingLayoutSettings = $this->normalizeSettingArray($box->layout_settings);
                $requestLayoutSettings = $this->normalizeSettingArray($request->layout_settings);
                $updateData['layout_settings'] = array_merge($existingLayoutSettings, $requestLayoutSettings);
            }
            
            if ($request->has('is_active')) {
                $updateData['is_active'] = $request->input('is_active', 0) == 1 || $request->boolean('is_active');
            }

            $box->update($updateData);

            // Ensure boxes-based styling is enabled so changes are visible on the timetable preview
            Setting::set('use_boxes_styling', 'enabled', 'string', 'Use boxes-based styling (enabled|disabled)');

            return response()->json([
                'success' => true,
                'message' => 'Box settings updated successfully.',
                'box' => $box->fresh()
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'error' => 'Preview update failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset box to default settings
     */
    public function reset(Request $request, $boxType)
    {
        $defaults = BoxSetting::getDefaultBoxSettings();
        
        if (!isset($defaults[$boxType])) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid box type.'], 404);
            }
            return redirect()->route('admin.boxes.index')->with('error', 'Invalid box type.');
        }

        if (in_array($boxType, ['welcome_box', 'note_prayer_box'])) {
            return response()->json(['error' => 'This box is not toggleable.'], 403);
        }
        $box = BoxSetting::where('box_type', $boxType)->first();
        $defaultBox = $defaults[$boxType];
        
        if ($box) {
            $box->update([
                'box_name' => $defaultBox['box_name'],
                'content_settings' => $defaultBox['content_settings'],
                'styling_settings' => $defaultBox['styling_settings'],
                'layout_settings' => $defaultBox['layout_settings'],
                'is_active' => true
            ]);
        } else {
            BoxSetting::create([
                'box_type' => $boxType,
                'box_name' => $defaultBox['box_name'],
                'content_settings' => $defaultBox['content_settings'],
                'styling_settings' => $defaultBox['styling_settings'],
                'layout_settings' => $defaultBox['layout_settings'],
                'is_active' => true,
                'sort_order' => array_search($boxType, array_keys($defaults))
            ]);
        }

        // Ensure boxes-based styling is enabled so the reset is visible on the timetable preview
        Setting::set('use_boxes_styling', 'enabled', 'string', 'Use boxes-based styling (enabled|disabled)');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Box reset to default settings successfully.'
            ]);
        }

        return redirect()->route('admin.boxes.index')
            ->with('success', 'Box reset to default settings successfully.');
    }

    /**
     * Toggle box active status
     */
    public function toggleActive($boxType)
    {
        $box = BoxSetting::where('box_type', $boxType)->first();
        
        if (!$box) {
            return response()->json(['error' => 'Box not found.'], 404);
        }

        $box->update(['is_active' => !$box->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $box->is_active,
            'message' => $box->is_active ? 'Box activated.' : 'Box deactivated.'
        ]);
    }

    /**
     * Get box preview data
     */
    public function getPreview($boxType)
    {
        $box = BoxSetting::where('box_type', $boxType)->first();
        
        if (!$box) {
            $defaults = BoxSetting::getDefaultBoxSettings();
            $boxData = $defaults[$boxType] ?? null;
        } else {
            $boxData = [
                'box_name' => $box->box_name,
                'content_settings' => $box->content_settings ?? [],
                'styling_settings' => $box->styling_settings ?? [],
                'layout_settings' => $box->layout_settings ?? []
            ];
        }

        return response()->json($boxData);
    }

    /**
     * Get all boxes for preview
     */
    public function getAllBoxes()
    {
        $boxes = BoxSetting::getAllActiveSettings();
        return response()->json($boxes);
    }

    /**
     * Update box order
     */
    public function updateOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'boxes' => 'required|array',
            'boxes.*.id' => 'required|integer|exists:box_settings,id',
            'boxes.*.sort_order' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->boxes as $boxData) {
            BoxSetting::where('id', $boxData['id'])
                ->update(['sort_order' => $boxData['sort_order']]);
        }

        return response()->json(['success' => true, 'message' => 'Box order updated successfully.']);
    }

    /**
     * Initialize default box settings
     */
    public function initializeDefaults()
    {
        try {
            BoxSetting::initializeDefaults();
            // Enable boxes styling so the initialized defaults are immediately visible
            Setting::set('use_boxes_styling', 'enabled', 'string', 'Use boxes-based styling (enabled|disabled)');
            return response()->json(['success' => true, 'message' => 'All box configurations cleared and reset to factory defaults. Box settings are now active.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to initialize defaults: ' . $e->getMessage()], 500);
        }
    }
}
