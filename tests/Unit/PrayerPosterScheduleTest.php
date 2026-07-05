<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Services\MediaDisplayService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrayerPosterScheduleTest extends TestCase
{
    private const TZ = 'Europe/London';

    private MediaDisplayService $service;

    private string $date = '2026-07-05';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('media_schedule_media');
        Schema::dropIfExists('media_schedules');
        Schema::dropIfExists('media');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('prayer_times');

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('prayer_times', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('zohar')->nullable();
            $table->time('zohar_jamaat')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('type')->default('image');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_duration')->default(30);
            $table->timestamps();
        });

        Schema::create('media_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_type');
            $table->string('prayer_name')->nullable();
            $table->integer('minutes_before_prayer')->nullable();
            $table->integer('minutes_after_prayer')->nullable();
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('media_schedule_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_schedule_id');
            $table->foreignId('media_id');
            $table->decimal('duration', 8, 2)->default(1);
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('expiry_date')->nullable();
            $table->time('expiry_time')->nullable();
            $table->integer('gap_duration')->default(0);
            $table->json('days_of_week')->nullable();
            $table->timestamps();
        });

        Setting::set('timezone', self::TZ);

        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => '12:30:00',
            'zohar_jamaat' => '13:15:00',
        ]);

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => 10,
            'is_active' => true,
        ]);

        $media = Media::query()->create([
            'title' => 'Test Poster',
            'file_path' => 'media/test.png',
            'type' => 'image',
            'is_active' => true,
            'display_duration' => 0.5,
        ]);

        $schedule->mediaItems()->attach($media->id, [
            'duration' => 0.5,
            'priority' => 1,
            'is_active' => true,
            'gap_duration' => 0,
        ]);

        $this->service = app(MediaDisplayService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_before_prayer_poster_loops_for_entire_schedule_window(): void
    {
        // Window: 13:05 - 13:10 (10 min before, stops 5 min before Jamaat)
        $insideWindow = Carbon::parse('2026-07-05 13:07:45', self::TZ);
        $afterMediaDuration = Carbon::parse('2026-07-05 13:05:45', self::TZ);

        Carbon::setTestNow($insideWindow);
        $this->assertNotNull($this->service->getCurrentMedia(), 'Poster should still be active late in the window');

        Carbon::setTestNow($afterMediaDuration);
        $this->assertNotNull($this->service->getCurrentMedia(), 'Poster should loop after its 30-second duration');
    }

    public function test_before_prayer_poster_inactive_outside_schedule_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 13:11:00', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());
    }
}
