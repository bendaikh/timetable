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
        // First, delete any existing schedules with time_range or countdown types
        DB::table('media_schedules')->whereIn('schedule_type', ['time_range', 'countdown'])->delete();
        
        // Update the enum to remove time_range and countdown
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'minutes_before_prayer'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the enum to include time_range and countdown
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'time_range', 'countdown', 'minutes_before_prayer'])->change();
        });
    }
};