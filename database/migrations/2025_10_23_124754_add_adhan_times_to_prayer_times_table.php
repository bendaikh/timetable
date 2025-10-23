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
            $table->time('fajr_adhan')->nullable()->after('fajr');
            $table->time('zohar_adhan')->nullable()->after('zohar');
            $table->time('asr_adhan')->nullable()->after('asr');
            $table->time('maghrib_adhan')->nullable()->after('maghrib');
            $table->time('isha_adhan')->nullable()->after('isha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prayer_times', function (Blueprint $table) {
            $table->dropColumn(['fajr_adhan', 'zohar_adhan', 'asr_adhan', 'maghrib_adhan', 'isha_adhan']);
        });
    }
};
