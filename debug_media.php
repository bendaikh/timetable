<?php

require_once 'vendor/autoload.php';

use App\Models\PrayerTime;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Debugging media display issue...\n";

// Get today's prayer times
$prayerTime = PrayerTime::getTodayPrayerTimes();
if (!$prayerTime) {
    echo "No prayer times found for today\n";
    exit;
}

echo "Asr time: " . $prayerTime->asr . "\n";

// Calculate display time for 88 minutes before Asr
$asrTime = Carbon::parse($prayerTime->asr);
$displayTime = $asrTime->copy()->subMinutes(88);
echo "Display time for 88 minutes before Asr: " . $displayTime->format('H:i:s') . "\n";

echo "Current time: " . now()->format('H:i:s') . "\n";

// Check if current time is within the display window
$isActive = now()->between($displayTime, $asrTime->copy()->subMinutes(5));
echo "Is media active now: " . ($isActive ? 'Yes' : 'No') . "\n";

// Check active schedules
$schedules = App\Models\MediaSchedule::active()->with('media')->get();
foreach ($schedules as $schedule) {
    echo "Schedule ID: " . $schedule->id . "\n";
    echo "  Prayer: " . $schedule->prayer_name . "\n";
    echo "  Minutes before: " . $schedule->minutes_before_prayer . "\n";
    echo "  Media active: " . ($schedule->media->is_active ? 'Yes' : 'No') . "\n";
    echo "  Media title: " . $schedule->media->title . "\n";
    echo "  Media file path: " . $schedule->media->file_path . "\n";
    echo "  Media type: " . $schedule->media->type . "\n";
    echo "  Is active for minutes before prayer: " . ($schedule->isActiveForMinutesBeforePrayer() ? 'Yes' : 'No') . "\n";

    if ($schedule->media->type === 'image') {
        $expectedPath = 'storage/' . $schedule->media->file_path;
        echo "  Expected file path: " . $expectedPath . "\n";
        echo "  File exists: " . (file_exists(public_path($expectedPath)) ? 'Yes' : 'No') . "\n";
    }
}
