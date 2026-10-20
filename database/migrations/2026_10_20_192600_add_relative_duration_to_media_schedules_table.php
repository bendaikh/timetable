<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add relative_duration when media_schedules was created before that column
     * was written into the original create migration. Display duration is stored
     * here in minutes.
     */
    public function up(): void
    {
        if (Schema::hasColumn('media_schedules', 'relative_duration')) {
            return;
        }

        Schema::table('media_schedules', function (Blueprint $table) {
            $table->integer('relative_duration')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('media_schedules', 'relative_duration')) {
            return;
        }

        Schema::table('media_schedules', function (Blueprint $table) {
            $table->dropColumn('relative_duration');
        });
    }
};
