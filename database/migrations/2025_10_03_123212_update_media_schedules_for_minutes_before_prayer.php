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
        Schema::table('media_schedules', function (Blueprint $table) {
            // Add minutes_before_prayer field
            $table->integer('minutes_before_prayer')->nullable()->after('prayer_name');
            
            // Make priority unique
            $table->unique('priority');
            
            // Update schedule_type enum to include new type
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'time_range', 'countdown', 'minutes_before_prayer'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->dropColumn('minutes_before_prayer');
            $table->dropUnique(['priority']);
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'time_range', 'countdown'])->change();
        });
    }
};