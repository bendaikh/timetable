<?php

require_once 'vendor/autoload.php';

use App\Models\PrayerTime;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$prayerTime = PrayerTime::getTodayPrayerTimes();
$asrTime = Carbon::parse($prayerTime->asr);
$displayEndTime = $asrTime->copy()->subMinutes(5);
$currentTime = Carbon::now();

echo "Asr time: " . $asrTime->format('H:i:s') . PHP_EOL;
echo "Display window ends at: " . $displayEndTime->format('H:i:s') . PHP_EOL;
echo "Current time: " . $currentTime->format('H:i:s') . PHP_EOL;
echo "Is past end time: " . ($currentTime->gt($displayEndTime) ? 'Yes' : 'No') . PHP_EOL;
