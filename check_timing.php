<?php

require_once 'vendor/autoload.php';

use App\Models\PrayerTime;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$prayerTime = PrayerTime::getTodayPrayerTimes();
$asrTime = Carbon::parse($prayerTime->asr);
$displayTime = $asrTime->copy()->subMinutes(74);
$displayEndTime = $asrTime->copy()->subMinutes(5);
$currentTime = Carbon::now();

echo "Asr time: " . $asrTime->format('H:i:s') . PHP_EOL;
echo "Display start: " . $displayTime->format('H:i:s') . PHP_EOL;
echo "Display end (5 min before Asr): " . $displayEndTime->format('H:i:s') . PHP_EOL;
echo "Current time: " . $currentTime->format('H:i:s') . PHP_EOL;
echo "Is media active: " . ($currentTime->between($displayTime, $displayEndTime) ? 'Yes' : 'No') . PHP_EOL;
