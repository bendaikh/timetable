<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add special-time columns when prayer_times was created before they existed
     * in the original migration (common on older local SQLite databases).
     */
    public function up(): void
    {
        Schema::table('prayer_times', function (Blueprint $table) {
            if (!Schema::hasColumn('prayer_times', 'sun_rise')) {
                $table->time('sun_rise')->nullable();
            }
            if (!Schema::hasColumn('prayer_times', 'jumah_1')) {
                $table->time('jumah_1')->nullable();
            }
            if (!Schema::hasColumn('prayer_times', 'jumah_2')) {
                $table->time('jumah_2')->nullable();
            }
            if (!Schema::hasColumn('prayer_times', 'eid_prayer_1')) {
                $table->time('eid_prayer_1')->nullable();
            }
            if (!Schema::hasColumn('prayer_times', 'eid_prayer_2')) {
                $table->time('eid_prayer_2')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('prayer_times', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('prayer_times', 'sun_rise') ? 'sun_rise' : null,
                Schema::hasColumn('prayer_times', 'jumah_1') ? 'jumah_1' : null,
                Schema::hasColumn('prayer_times', 'jumah_2') ? 'jumah_2' : null,
                Schema::hasColumn('prayer_times', 'eid_prayer_1') ? 'eid_prayer_1' : null,
                Schema::hasColumn('prayer_times', 'eid_prayer_2') ? 'eid_prayer_2' : null,
            ]);

            if ($columns !== []) {
                $table->dropColumn(array_values($columns));
            }
        });
    }
};
