<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, delete any existing schedules with prayer_before or prayer_after types
        DB::table('media_schedules')->whereIn('schedule_type', ['prayer_before', 'prayer_after'])->delete();
        
        // Update the enum to only include minutes_before_prayer and minutes_after_prayer
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->enum('schedule_type', ['minutes_before_prayer', 'minutes_after_prayer'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the enum to include all types
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'minutes_before_prayer', 'minutes_after_prayer'])->change();
        });
    }
};