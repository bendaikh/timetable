<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BoxSetting;

class UpdateTimetableBackground extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timetable:update-background {color=#e6f3ff}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the timetable background color';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $color = $this->argument('color');
        
        // Validate color format
        if (!preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $this->error('Invalid color format. Please use hex format like #e6f3ff');
            return 1;
        }
        
        $box = BoxSetting::where('box_type', 'timetable_background_box')->first();
        
        if (!$box) {
            $this->error('Timetable background box not found!');
            return 1;
        }
        
        $currentColor = $box->styling_settings['background_color'] ?? 'Not set';
        $this->info("Current background color: {$currentColor}");
        
        // Update the background color
        $stylingSettings = $box->styling_settings ?? [];
        $stylingSettings['background_color'] = $color;
        $box->styling_settings = $stylingSettings;
        $box->save();
        
        $this->info("Timetable background color updated to: {$color}");
        $this->info("Please refresh your browser to see the changes.");
        
        return 0;
    }
}