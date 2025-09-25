<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create prayer times for today and next few days
        $dates = [
            now()->format('Y-m-d'),
            now()->copy()->addDay()->format('Y-m-d'),
            now()->copy()->addDays(2)->format('Y-m-d'),
        ];

        foreach ($dates as $date) {
            \App\Models\PrayerTime::updateOrCreate(
                ['date' => $date],
                [
                    'date' => $date,
                    'fajr' => '05:30:00',
                    'zohar' => '13:15:00',
                    'asr' => '17:11:00',
                    'maghrib' => '20:34:00',
                    'isha' => '21:50:00',
                    'sun_rise' => '05:51:00',
                    'jumah_1' => '13:30:00',
                    'jumah_2' => '13:30:00',
                ]
            );
        }

        // Create sample hadeeths
        $hadeeths = [
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: إِنَّمَا الْأَعْمَالُ بِالنِّيَّاتِ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "Actions are but by intention."',
                'reference' => 'Sahih Bukhari 1',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: مَنْ كَانَ يُؤْمِنُ بِاللَّهِ وَالْيَوْمِ الْآخِرِ فَلْيَقُلْ خَيْرًا أَوْ لِيَصْمُتْ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "Whoever believes in Allah and the Last Day should speak good or remain silent."',
                'reference' => 'Sahih Bukhari 6018',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: الْمُسْلِمُ مَنْ سَلِمَ الْمُسْلِمُونَ مِنْ لِسَانِهِ وَيَدِهِ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "A Muslim is one from whose tongue and hand the Muslims are safe."',
                'reference' => 'Sahih Bukhari 10',
                'is_active' => true,
                'display_order' => 3,
            ],
        ];

        foreach ($hadeeths as $hadeeth) {
            \App\Models\Hadeeth::create($hadeeth);
        }

        // Create sample announcements
        $announcements = [
            [
                'title' => 'Friday Prayer Announcement',
                'content' => 'Jumah prayers will be held at 1:30 PM. Please arrive early for congregational prayer.',
                'is_active' => true,
                'auto_repeat' => true,
                'repeat_days' => ['friday'],
                'display_duration' => 15,
                'font_size' => 24,
                'text_color' => '#000000',
                'background_color' => '#ffffff',
                'scroll_speed' => 3,
            ],
            [
                'title' => 'Ramadan Schedule',
                'content' => 'During Ramadan, Tarawih prayers will be held after Isha prayer. All are welcome.',
                'is_active' => true,
                'auto_repeat' => false,
                'display_duration' => 20,
                'font_size' => 22,
                'text_color' => '#000000',
                'background_color' => '#f0f8ff',
                'scroll_speed' => 2,
            ],
        ];

        foreach ($announcements as $announcement) {
            \App\Models\Announcement::create($announcement);
        }

        // Initialize default settings
        \App\Models\Setting::initializeDefaults();

        echo "Sample data created successfully!\n";
    }
}
