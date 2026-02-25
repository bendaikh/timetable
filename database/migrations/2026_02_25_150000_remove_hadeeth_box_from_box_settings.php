<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('box_settings')
            ->where('box_type', 'hadeeth_box')
            ->delete();
    }

    public function down(): void
    {
        $exists = DB::table('box_settings')
            ->where('box_type', 'hadeeth_box')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('box_settings')->insert([
            'box_type' => 'hadeeth_box',
            'box_name' => 'Hadeeth Box',
            'content_settings' => json_encode([
                'title' => 'Hadeeth Of The Day',
            ]),
            'styling_settings' => json_encode([
                'background_color' => 'rgba(253, 247, 230, 0.9)',
                'text_color' => '#000000',
                'title_color' => '#000000',
            ]),
            'layout_settings' => json_encode([]),
            'is_active' => false,
            'sort_order' => 999,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
