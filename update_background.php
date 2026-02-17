<?php

// Simple script to update timetable background color
// Run this directly on your production server: php update_background.php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Updating timetable background color...\n";

$box = \App\Models\BoxSetting::where('box_type', 'timetable_background_box')->first();

if (!$box) {
    echo "ERROR: Timetable background box not found!\n";
    exit(1);
}

$currentColor = $box->styling_settings['background_color'] ?? 'Not set';
echo "Current background color: {$currentColor}\n";

// Update to a more visible light blue color
$newColor = '#e6f3ff';
$stylingSettings = $box->styling_settings ?? [];
$stylingSettings['background_color'] = $newColor;
$box->styling_settings = $stylingSettings;
$box->save();

echo "Timetable background color updated to: {$newColor}\n";
echo "Please refresh your browser to see the changes.\n";
echo "Done!\n";
