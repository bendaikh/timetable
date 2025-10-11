<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PrayerTime;
use App\Models\Setting;

echo "=== UPDATING EXISTING JAMAAT TIMES ===\n\n";

// Get offset settings
$fajrOffset = (int) Setting::get('fajr_jamaat_offset', 10);
$zoharOffset = (int) Setting::get('zohar_jamaat_offset', 15);
$asrOffset = (int) Setting::get('asr_jamaat_offset', 20);
$maghribOffset = (int) Setting::get('maghrib_jamaat_offset', 0);
$ishaOffset = (int) Setting::get('isha_jamaat_offset', 10);

echo "Current offset settings:\n";
echo "  Fajr: +{$fajrOffset} minutes\n";
echo "  Zohar: +{$zoharOffset} minutes\n";
echo "  Asr: +{$asrOffset} minutes\n";
echo "  Maghrib: +{$maghribOffset} minutes\n";
echo "  Isha: +{$ishaOffset} minutes\n\n";

// Get all prayer times that have NULL jamaat times
$prayerTimes = PrayerTime::whereNull('fajr_jamaat')
    ->orWhereNull('zohar_jamaat')
    ->orWhereNull('asr_jamaat')
    ->orWhereNull('maghrib_jamaat')
    ->orWhereNull('isha_jamaat')
    ->get();

echo "Found {$prayerTimes->count()} prayer time records with missing jamaat times.\n\n";

if ($prayerTimes->count() === 0) {
    echo "All prayer times already have jamaat times set!\n";
    exit(0);
}

$updated = 0;

foreach ($prayerTimes as $pt) {
    $updates = [];
    
    // Update jamaat times using offsets if they're NULL
    if (is_null($pt->fajr_jamaat) && !is_null($pt->fajr)) {
        $updates['fajr_jamaat'] = \Carbon\Carbon::parse($pt->fajr)->addMinutes($fajrOffset)->format('H:i:s');
    }
    
    if (is_null($pt->zohar_jamaat) && !is_null($pt->zohar)) {
        $updates['zohar_jamaat'] = \Carbon\Carbon::parse($pt->zohar)->addMinutes($zoharOffset)->format('H:i:s');
    }
    
    if (is_null($pt->asr_jamaat) && !is_null($pt->asr)) {
        $updates['asr_jamaat'] = \Carbon\Carbon::parse($pt->asr)->addMinutes($asrOffset)->format('H:i:s');
    }
    
    if (is_null($pt->maghrib_jamaat) && !is_null($pt->maghrib)) {
        $updates['maghrib_jamaat'] = \Carbon\Carbon::parse($pt->maghrib)->addMinutes($maghribOffset)->format('H:i:s');
    }
    
    if (is_null($pt->isha_jamaat) && !is_null($pt->isha)) {
        $updates['isha_jamaat'] = \Carbon\Carbon::parse($pt->isha)->addMinutes($ishaOffset)->format('H:i:s');
    }
    
    if (!empty($updates)) {
        $pt->update($updates);
        $updated++;
        echo "Updated {$pt->date}: " . implode(', ', array_keys($updates)) . "\n";
    }
}

echo "\n=== COMPLETED ===\n";
echo "Updated {$updated} prayer time records.\n";
echo "All jamaat times have been set using the current offset settings.\n\n";

echo "You can now:\n";
echo "1. Re-import your prayer times file (it will now include jamaat times)\n";
echo "2. Or manually edit specific jamaat times in the admin panel\n";
echo "3. Or adjust the offset settings if needed\n";
