<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->integer('minutes_after_prayer')->nullable()->after('minutes_before_prayer');
        });
    }

    public function down(): void
    {
        Schema::table('media_schedules', function (Blueprint $table) {
            $table->dropColumn('minutes_after_prayer');
        });
    }
};




