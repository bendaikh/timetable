<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PrayerTime;
use App\Models\Setting;
use Carbon\Carbon;

$tz = 'Europe/London';
Setting::set('timezone', $tz, 'string', 'Mosque display timezone (UK)');

$now = Carbon::now($tz);
$today = $now->toDateString();
$leadMinutes = max(22, (int) (getenv('COUNTDOWN_LEAD_MINUTES') ?: 22));
$jamaat = $now->copy()->addMinutes($leadMinutes)->second(0);

$rows = PrayerTime::whereDate('date', $today)->orderBy('id')->get();
$row = $rows->first();

if (!$row) {
    $row = PrayerTime::create(['date' => $today]);
}

foreach ($rows->skip(1) as $duplicate) {
    $duplicate->delete();
}

$row->update([
    'fajr' => '05:00:00',
    'fajr_jamaat' => '05:10:00',
    'zohar' => '12:30:00',
    'zohar_jamaat' => '12:45:00',
    'asr' => '16:00:00',
    'asr_jamaat' => $jamaat->copy()->subHours(3)->format('H:i:s'),
    'maghrib' => '19:00:00',
    'maghrib_adhan' => '19:05:00',
    'maghrib_jamaat' => $jamaat->format('H:i:s'),
    'isha' => '21:00:00',
    'isha_jamaat' => $jamaat->copy()->addHours(3)->format('H:i:s'),
]);

$adhanStart = $jamaat->copy()->subMinutes(20);
$adhanEnd = $adhanStart->copy()->addSeconds(30);
$iqamahStart = $jamaat->copy()->subSeconds(30);

echo json_encode([
    'timezone' => $tz,
    'setup_at' => $now->toIso8601String(),
    'prayer_date' => $today,
    'test_prayer' => 'maghrib',
    'jamaat_at' => $jamaat->toIso8601String(),
    'countdown_1_start' => $adhanStart->toIso8601String(),
    'countdown_1_end' => $adhanEnd->toIso8601String(),
    'countdown_2_start' => $iqamahStart->toIso8601String(),
    'countdown_2_end' => $jamaat->toIso8601String(),
], JSON_PRETTY_PRINT) . PHP_EOL;
