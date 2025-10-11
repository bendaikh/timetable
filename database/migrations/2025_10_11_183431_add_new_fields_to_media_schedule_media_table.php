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
        Schema::table('media_schedule_media', function (Blueprint $table) {
            // Expiry date and time for Full Time Poster
            $table->date('expiry_date')->nullable()->after('priority');
            $table->time('expiry_time')->nullable()->after('expiry_date');
            
            // Gap duration between medias (in seconds) for Full Time Poster
            $table->integer('gap_duration')->nullable()->default(0)->after('expiry_time');
            
            // Days of week for each media (moved from schedule level to media level)
            $table->json('days_of_week')->nullable()->after('gap_duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_schedule_media', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'expiry_time', 'gap_duration', 'days_of_week']);
        });
    }
};
