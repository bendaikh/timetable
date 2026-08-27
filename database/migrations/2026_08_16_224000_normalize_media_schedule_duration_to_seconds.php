<?php

use App\Support\MediaScheduleDuration;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalize media_schedule_media.duration to integer seconds.
 * Legacy admin UI stored minutes (0.5, 1, 2…); recent code briefly stored seconds/60.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('media_schedule_media')) {
            return;
        }

        $rows = DB::table('media_schedule_media')->select('id', 'duration')->get();
        // Keep a fixed legacy threshold of 10 even if MIN_SECONDS is lowered later
        // (e.g. to 5), so old minute values like 5 are still converted to 300s.
        $legacyThreshold = 10;
        foreach ($rows as $row) {
            $raw = (float) $row->duration;
            if ($raw <= 0) {
                $seconds = MediaScheduleDuration::DEFAULT_SECONDS;
            } elseif ($raw < $legacyThreshold) {
                // Legacy minutes (including fractional 10s→0.166… that survived on SQLite)
                $seconds = (int) max(MediaScheduleDuration::MIN_SECONDS, round($raw * 60));
            } else {
                // Already seconds (or legacy whole minutes >= 10 — treat as minutes only if huge?)
                // Values 10–480 were common as minutes in old UI; 10–28800 as seconds in new UI.
                // Prefer: if value looks like old minutes (10, 15, 20, 30, 60) AND we can't know,
                // keep as seconds going forward (admin can re-save). Production issue was *skipping*
                // posters, not this conversion edge case.
                $seconds = (int) max(MediaScheduleDuration::MIN_SECONDS, round($raw));
            }

            DB::table('media_schedule_media')->where('id', $row->id)->update([
                'duration' => $seconds,
            ]);
        }
    }

    public function down(): void
    {
        // Irreversible normalization — leave seconds in place.
    }
};
