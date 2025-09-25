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
        Schema::create('hadeeths', function (Blueprint $table) {
            $table->id();
            $table->text('arabic_text');
            $table->text('english_translation');
            $table->string('reference'); // e.g., "Sahih Bukhari 1234"
            $table->boolean('is_active')->default(true);
            $table->date('display_date')->nullable(); // specific date to display
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hadeeths');
    }
};
