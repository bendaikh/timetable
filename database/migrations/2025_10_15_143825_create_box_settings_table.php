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
        Schema::create('box_settings', function (Blueprint $table) {
            $table->id();
            $table->string('box_type'); // header_box, prayer_times_box, note_prayer_box, hadeeth_box, announcements_box, donation_box, welcome_box
            $table->string('box_name');
            $table->json('content_settings')->nullable(); // Text content, time formats, character limits
            $table->json('styling_settings')->nullable(); // Colors, fonts, borders, padding
            $table->json('layout_settings')->nullable(); // Position, size, alignment
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->unique('box_type');
            $table->index(['is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('box_settings');
    }
};
