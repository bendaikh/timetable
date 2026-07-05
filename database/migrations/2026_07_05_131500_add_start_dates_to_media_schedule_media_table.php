<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_schedule_media', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('priority');
            $table->time('start_time')->nullable()->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('media_schedule_media', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'start_time']);
        });
    }
};
