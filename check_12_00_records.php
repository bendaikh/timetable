<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Set SQLite connection
config(['database.default' => 'sqlite']);
if(!getenv('DB_DATABASE')) {
    putenv('DB_DATABASE=database/database.sqlite');
}

echo "Checking for 12:00 records...\n";

$count = App\Models\PrayerTime::where('fajr', '12:00:00')
    ->orWhere('zohar', '12:00:00')
    ->orWhere('asr', '12:00:00')
    ->orWhere('maghrib', '12:00:00')
    ->orWhere('isha', '12:00:00')
    ->count();

echo "Records with 12:00: " . $count . "\n";

if ($count > 0) {
    echo "\nFound records with 12:00:\n";
    $records = App\Models\PrayerTime::where('fajr', '12:00:00')
        ->orWhere('zohar', '12:00:00')
        ->orWhere('asr', '12:00:00')
        ->orWhere('maghrib', '12:00:00')
        ->orWhere('isha', '12:00:00')
        ->limit(5)
        ->get();
    
    foreach($records as $record) {
        echo "ID: " . $record->id . " - Date: " . $record->date . " - Fajr: " . $record->fajr . ", Zohar: " . $record->zohar . ", Asr: " . $record->asr . ", Maghrib: " . $record->maghrib . ", Isha: " . $record->isha . "\n";
    }
}

echo "\nLatest 3 records:\n";
$latest = App\Models\PrayerTime::orderBy('date', 'desc')->limit(3)->get();
foreach($latest as $record) {
    echo "ID: " . $record->id . " - Date: " . $record->date . " - Fajr: " . $record->fajr . ", Zohar: " . $record->zohar . ", Asr: " . $record->asr . ", Maghrib: " . $record->maghrib . ", Isha: " . $record->isha . "\n";
}

