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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_repeat')->default(false);
            $table->json('repeat_days')->nullable(); // ['monday', 'tuesday', ...]
            $table->datetime('start_date')->nullable();
            $table->datetime('end_date')->nullable();
            $table->integer('display_duration')->default(10); // seconds
            $table->integer('font_size')->default(24);
            $table->string('text_color')->default('#000000');
            $table->string('background_color')->default('#ffffff');
            $table->integer('scroll_speed')->default(3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
