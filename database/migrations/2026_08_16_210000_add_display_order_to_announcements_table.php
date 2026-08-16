<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->unsignedInteger('display_order')->default(1)->after('priority');
        });

        $announcements = DB::table('announcements')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id']);

        foreach ($announcements as $index => $row) {
            DB::table('announcements')
                ->where('id', $row->id)
                ->update(['display_order' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('display_order');
        });
    }
};
