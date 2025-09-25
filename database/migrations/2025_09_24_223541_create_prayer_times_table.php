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
        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('fajr');
            $table->time('zohar');
            $table->time('asr');
            $table->time('maghrib');
            $table->time('isha');
            $table->time('sun_rise')->nullable();
            $table->time('jumah_1')->nullable();
            $table->time('jumah_2')->nullable();
            $table->time('eid_prayer_1')->nullable();
            $table->time('eid_prayer_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_times');
    }
};
