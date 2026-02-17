<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prayer_times', function (Blueprint $table) {
            $table->time('fajr_jamaat')->nullable()->after('fajr');
            $table->time('zohar_jamaat')->nullable()->after('zohar');
            $table->time('asr_jamaat')->nullable()->after('asr');
            $table->time('maghrib_jamaat')->nullable()->after('maghrib');
            $table->time('isha_jamaat')->nullable()->after('isha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_times', function (Blueprint $table) {
            $table->dropColumn([
                'fajr_jamaat',
                'zohar_jamaat',
                'asr_jamaat',
                'maghrib_jamaat',
                'isha_jamaat'
            ]);
        });
    }
};
