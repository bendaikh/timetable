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
        Schema::create('media_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->enum('schedule_type', ['prayer_before', 'prayer_after', 'time_range', 'countdown']);
            $table->enum('prayer_name', ['fajr', 'zohar', 'asr', 'maghrib', 'isha'])->nullable();
            $table->json('days_of_week')->nullable(); // [1,2,3,4,5,6,7] for days
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('relative_duration')->nullable(); // seconds before/after prayer
            $table->integer('countdown_duration')->default(30); // For countdown before adhan
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_schedules');
    }
};
