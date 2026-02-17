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
        Schema::create('sliding_texts', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->boolean('is_active')->default(true);
            $table->integer('animation_speed')->default(20); // seconds
            $table->integer('font_size')->default(14);
            $table->string('font_weight')->default('700'); // bold
            $table->string('text_color')->default('#000000'); // black
            $table->string('background_color')->default('rgba(255, 255, 255, 0.95)');
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliding_texts');
    }
};
