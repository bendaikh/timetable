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
        Schema::create('media_schedule_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('media_id')->constrained()->onDelete('cascade');
            $table->integer('duration')->default(30); // Duration in seconds for this media in this schedule
            $table->integer('priority')->default(1); // Display order within the schedule (1 = first, 2 = second, etc.)
            $table->timestamps();
            
            // Ensure unique combination of schedule and media
            $table->unique(['media_schedule_id', 'media_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_schedule_media');
    }
};
