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
        echo "Creating prayer times for the next 30 days...\n";
        
        // Create prayer times for the next 30 days with realistic variations
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i);
            $dateString = $date->format('Y-m-d');
            
            // Calculate realistic prayer times that gradually change throughout the month
            $fajrMinutes = 330 + ($i * 0.5); // Fajr gradually gets later
            $zoharMinutes = 795; // Zohar stays relatively stable (13:15)
            $asrMinutes = 1020 + ($i * 0.3); // Asr gradually gets later
            $maghribMinutes = 1200 + ($i * 0.8); // Maghrib changes more noticeably
            $ishaMinutes = 1310 + ($i * 0.6); // Isha follows Maghrib
            $sunriseMinutes = 351 + ($i * 0.4); // Sunrise gradually gets later
            
            $fajr = sprintf('%02d:%02d:00', floor($fajrMinutes / 60), $fajrMinutes % 60);
            $zohar = sprintf('%02d:%02d:00', floor($zoharMinutes / 60), $zoharMinutes % 60);
            $asr = sprintf('%02d:%02d:00', floor($asrMinutes / 60), $asrMinutes % 60);
            $maghrib = sprintf('%02d:%02d:00', floor($maghribMinutes / 60), $maghribMinutes % 60);
            $isha = sprintf('%02d:%02d:00', floor($ishaMinutes / 60), $ishaMinutes % 60);
            $sunrise = sprintf('%02d:%02d:00', floor($sunriseMinutes / 60), $sunriseMinutes % 60);
            
            // Jumah is only on Fridays
            $jumah1 = $date->dayOfWeek === 5 ? '13:30:00' : null; // Friday = 5
            $jumah2 = $date->dayOfWeek === 5 ? '14:00:00' : null;
            
            \App\Models\PrayerTime::updateOrCreate(
                ['date' => $dateString],
                [
                    'date' => $dateString,
                    'fajr' => $fajr,
                    'zohar' => $zohar,
                    'asr' => $asr,
                    'maghrib' => $maghrib,
                    'isha' => $isha,
                    'sun_rise' => $sunrise,
                    'jumah_1' => $jumah1,
                    'jumah_2' => $jumah2,
                ]
            );
        }
        
        echo "Prayer times created successfully!\n";

        echo "Creating hadeeths...\n";
        
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
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: لَا يُؤْمِنُ أَحَدُكُمْ حَتَّى يُحِبَّ لِأَخِيهِ مَا يُحِبُّ لِنَفْسِهِ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "None of you truly believes until he loves for his brother what he loves for himself."',
                'reference' => 'Sahih Bukhari 13',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: مَنْ لَا يَرْحَمُ النَّاسَ لَا يَرْحَمُهُ اللَّهُ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "Whoever does not show mercy to people, Allah will not show mercy to him."',
                'reference' => 'Sahih Bukhari 7376',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: خَيْرُ النَّاسِ أَنْفَعُهُمْ لِلنَّاسِ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "The best of people are those who are most beneficial to others."',
                'reference' => 'Daraqutni, Hasan',
                'is_active' => true,
                'display_order' => 6,
            ],
            [
                'arabic_text' => 'قَالَ رَسُولُ اللَّهِ صَلَّى اللَّهُ عَلَيْهِ وَسَلَّمَ: الدِّينُ النَّصِيحَةُ',
                'english_translation' => 'The Messenger of Allah (peace be upon him) said: "Religion is sincere advice."',
                'reference' => 'Sahih Muslim 55',
                'is_active' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($hadeeths as $hadeeth) {
            \App\Models\Hadeeth::create($hadeeth);
        }

        echo "Creating announcements...\n";
        
        // Create sample announcements
        $announcements = [
            [
                'title' => 'Friday Prayer Announcement',
                'content' => 'Jumah prayers will be held at 1:30 PM and 2:00 PM. Please arrive early for congregational prayer.',
                'is_active' => true,
                'auto_repeat' => true,
                'repeat_days' => ['friday'],
                'display_duration' => 15,
                'font_size' => 24,
                'text_color' => '#ffffff',
                'background_color' => '#2c3e50',
                'scroll_speed' => 3,
            ],
            [
                'title' => 'Islamic Classes',
                'content' => 'Quran classes for children every Saturday and Sunday from 9:00 AM to 11:00 AM.',
                'is_active' => true,
                'auto_repeat' => true,
                'repeat_days' => ['saturday', 'sunday'],
                'display_duration' => 20,
                'font_size' => 22,
                'text_color' => '#000000',
                'background_color' => '#e8f5e8',
                'scroll_speed' => 2,
            ],
            [
                'title' => 'Community Iftar',
                'content' => 'Community Iftar every evening during Ramadan. All families are welcome to join.',
                'is_active' => true,
                'auto_repeat' => false,
                'display_duration' => 25,
                'font_size' => 20,
                'text_color' => '#ffffff',
                'background_color' => '#8e44ad',
                'scroll_speed' => 2,
            ],
            [
                'title' => 'Donation Appeal',
                'content' => 'Help support our masjid expansion project. Donations are greatly appreciated.',
                'is_active' => true,
                'auto_repeat' => false,
                'display_duration' => 18,
                'font_size' => 21,
                'text_color' => '#000000',
                'background_color' => '#ffeaa7',
                'scroll_speed' => 3,
            ],
            [
                'title' => 'Prayer Carpet Cleaning',
                'content' => 'Professional carpet cleaning scheduled for next Tuesday. Prayer hall will be closed from 9 AM to 2 PM.',
                'is_active' => true,
                'auto_repeat' => false,
                'display_duration' => 22,
                'font_size' => 19,
                'text_color' => '#ffffff',
                'background_color' => '#e74c3c',
                'scroll_speed' => 2,
            ],
            [
                'title' => 'Study Circle',
                'content' => 'Weekly study circle every Wednesday after Maghrib prayer. Topic: Understanding Islamic Ethics.',
                'is_active' => true,
                'auto_repeat' => true,
                'repeat_days' => ['wednesday'],
                'display_duration' => 20,
                'font_size' => 20,
                'text_color' => '#ffffff',
                'background_color' => '#3498db',
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
