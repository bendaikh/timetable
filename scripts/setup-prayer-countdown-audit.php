<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PrayerTime;
use App\Models\Setting;

Setting::set('timezone', 'Europe/London', 'string', 'Mosque display timezone (UK)');

$date = $argv[1] ?? '2026-06-24';

$rows = PrayerTime::whereDate('date', $date)->orderBy('id')->get();
$row = $rows->first();

if (!$row) {
    $row = PrayerTime::create(['date' => $date]);
}

foreach ($rows->skip(1) as $duplicate) {
    $duplicate->delete();
}

$row->update([
    'fajr' => '05:00:00',
    'fajr_jamaat' => '05:10:00',
    'zohar' => '12:00:00',
    'zohar_adhan' => '12:37:00',
    'zohar_jamaat' => '15:00:00',
    'asr' => '16:00:00',
    'asr_jamaat' => '16:15:00',
    'maghrib' => '19:00:00',
    'maghrib_adhan' => '19:05:00',
    'maghrib_jamaat' => '19:10:00',
    'isha' => '20:30:00',
    'isha_jamaat' => '20:45:00',
]);

echo $date . PHP_EOL;
