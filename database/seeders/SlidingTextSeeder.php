<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SlidingText;

class SlidingTextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SlidingText::create([
            'text' => 'Welcome to Masjid Admin Panel - Manage your prayer times, announcements, and media schedules',
            'is_active' => true,
            'animation_speed' => 20,
            'font_size' => 14,
            'font_weight' => '700',
            'text_color' => '#000000',
            'background_color' => 'rgba(255, 255, 255, 0.95)',
            'display_order' => 0,
        ]);
    }
}
