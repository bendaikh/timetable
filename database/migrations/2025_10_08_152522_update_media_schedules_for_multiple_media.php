<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('media_schedules', function (Blueprint $table) {
            // Remove media_id foreign key and column (will use pivot table instead)
            $table->dropForeign(['media_id']);
            $table->dropColumn('media_id');
            
            // Remove priority column (will be in pivot table per media)
            $table->dropColumn('priority');
        });
        
        // Update schedule_type enum to add 'full_time_poster'
        DB::statement("ALTER TABLE media_schedules MODIFY COLUMN schedule_type ENUM('minutes_before_prayer', 'minutes_after_prayer', 'full_time_poster') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_schedules', function (Blueprint $table) {
            // Add back media_id
            $table->foreignId('media_id')->nullable()->constrained()->onDelete('cascade');
            
            // Add back priority
            $table->integer('priority')->default(1);
        });
        
        // Revert schedule_type enum
        DB::statement("ALTER TABLE media_schedules MODIFY COLUMN schedule_type ENUM('minutes_before_prayer', 'minutes_after_prayer') NOT NULL");
    }
};
