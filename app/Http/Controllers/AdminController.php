<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PrayerTime;
use App\Models\Announcement;
use App\Models\Hadeeth;
use App\Models\Setting;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'prayer_times_count' => PrayerTime::count(),
            'announcements_count' => Announcement::where('is_active', true)->count(),
            'hadeeths_count' => Hadeeth::where('is_active', true)->count(),
            'media_count' => \App\Models\Media::where('is_active', true)->count(),
            'media_schedules_count' => \App\Models\MediaSchedule::where('is_active', true)->count(),
            'total_settings' => Setting::count(),
        ];

        $recent_announcements = Announcement::latest()->take(5)->get();
        $today_prayer_times = PrayerTime::getTodayPrayerTimes();
        $next_prayer = PrayerTime::getNextPrayer();

        return view('admin.dashboard', compact('stats', 'recent_announcements', 'today_prayer_times', 'next_prayer'));
    }
}
