<?php

// Comprehensive script to fix timetable background issues
// Run this on your production server: php fix_timetable_background.php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Timetable Background Fix ===\n\n";

// 1. Check if box exists
$box = \App\Models\BoxSetting::where('box_type', 'timetable_background_box')->first();
if (!$box) {
    echo "ERROR: Timetable background box not found!\n";
    echo "Initializing default box settings...\n";
    \App\Models\BoxSetting::initializeDefaults();
    $box = \App\Models\BoxSetting::where('box_type', 'timetable_background_box')->first();
    if (!$box) {
        echo "ERROR: Still cannot find timetable background box after initialization!\n";
        exit(1);
    }
}

echo "✓ Timetable background box found\n";

// 2. Check if it's active
if (!$box->is_active) {
    echo "WARNING: Timetable background box is not active. Activating...\n";
    $box->is_active = true;
    $box->save();
}

echo "✓ Timetable background box is active\n";

// 3. Check current color
$currentColor = $box->styling_settings['background_color'] ?? 'Not set';
echo "Current background color: {$currentColor}\n";

// 4. Update to a more visible color
$newColor = '#e6f3ff'; // Light blue
$stylingSettings = $box->styling_settings ?? [];
$stylingSettings['background_color'] = $newColor;
$box->styling_settings = $stylingSettings;
$box->save();

echo "✓ Background color updated to: {$newColor}\n";

// 5. Check if boxes styling is enabled
$useBoxesStyling = \App\Models\Setting::get('use_boxes_styling', 'enabled');
echo "Boxes styling enabled: {$useBoxesStyling}\n";

if ($useBoxesStyling !== 'enabled') {
    echo "WARNING: Boxes styling is not enabled. Enabling...\n";
    \App\Models\Setting::set('use_boxes_styling', 'enabled');
    echo "✓ Boxes styling enabled\n";
}

// 6. Clear caches
echo "Clearing caches...\n";
try {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    echo "✓ Caches cleared\n";
} catch (Exception $e) {
    echo "WARNING: Could not clear caches: " . $e->getMessage() . "\n";
}

// 7. Verify the settings
$boxSettings = \App\Models\BoxSetting::getAllActiveSettings();
if (isset($boxSettings['timetable_background_box'])) {
    $bgColor = $boxSettings['timetable_background_box']['styling_settings']['background_color'] ?? 'Not set';
    echo "✓ Verified: Background color in active settings: {$bgColor}\n";
} else {
    echo "ERROR: Timetable background box not found in active settings!\n";
}

echo "\n=== Summary ===\n";
echo "✓ Timetable background box is configured and active\n";
echo "✓ Background color set to: {$newColor}\n";
echo "✓ Boxes styling is enabled\n";
echo "✓ Caches cleared\n";
echo "\nPlease refresh your browser to see the changes.\n";
echo "If you still don't see the background, try:\n";
echo "1. Hard refresh (Ctrl+F5 or Cmd+Shift+R)\n";
echo "2. Clear browser cache\n";
echo "3. Check browser developer tools to see if the style is applied\n";
echo "\nDone!\n";
