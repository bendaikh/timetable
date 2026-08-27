<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Services\MediaDisplayService;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrayerPosterTimingTest extends TestCase
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

        $this->service = app(MediaDisplayService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_twenty_minutes_before_poster_starts_exactly_at_iqamah_minus_twenty(): void
    {
        $this->seedPosterSchedule(minutesBefore: 20, jamaat: '13:15:00');

        $schedule = MediaSchedule::query()->first();
        $prayerTime = PrayerTime::query()->first();
        $reference = Carbon::parse('2026-07-05 12:00:00', self::TZ);
        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $prayerTime, $reference);

        $this->assertNotNull($window);
        $this->assertSame('12:55:00', $window['start']->format('H:i:s'));

        $oneSecondEarly = Carbon::parse('2026-07-05 12:54:59', self::TZ);
        $insideWindowAfterCountdown = Carbon::parse('2026-07-05 12:56:00', self::TZ);

        Carbon::setTestNow($oneSecondEarly);
        $this->assertNull($this->service->getCurrentMedia(), 'Poster must not start before iqamah - 20 minutes');

        Carbon::setTestNow($insideWindowAfterCountdown);
        $this->assertNotNull(
            $this->service->getCurrentMedia(),
            'Poster must be active one minute after the configured start once countdown has finished'
        );
    }

    public function test_twenty_minutes_before_poster_is_inactive_twenty_two_minutes_early(): void
    {
        $this->seedPosterSchedule(minutesBefore: 20, jamaat: '13:15:00');

        Carbon::setTestNow(Carbon::parse('2026-07-05 12:53:00', self::TZ));
        $this->assertNull($this->service->getCurrentMedia(), 'Poster must not start 22 minutes before iqamah');
    }

    public function test_poster_window_uses_explicit_jamaat_column_not_beginning_plus_offset(): void
    {
        Setting::set('zohar_jamaat_offset', 15);

        $this->seedPosterSchedule(
            minutesBefore: 20,
            beginning: '12:30:00',
            jamaat: '13:15:00'
        );

        $schedule = MediaSchedule::query()->first();
        $prayerTime = PrayerTime::query()->first();
        $now = Carbon::parse('2026-07-05 12:00:00', self::TZ);

        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $prayerTime, $now);

        $this->assertNotNull($window);
        $this->assertSame('12:55:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:15:00', $window['end']->format('H:i:s'));
        $this->assertSame('13:15:00', $window['jamaat']->format('H:i:s'));
        $this->assertSame('jamaat', $window['reference']);
    }

    public function test_poster_window_falls_back_to_beginning_plus_offset_when_jamaat_missing(): void
    {
        Setting::set('zohar_jamaat_offset', 15);

        $this->seedPosterSchedule(
            minutesBefore: 20,
            beginning: '12:30:00',
            jamaat: null
        );

        $schedule = MediaSchedule::query()->first();
        $prayerTime = PrayerTime::query()->first();
        $now = Carbon::parse('2026-07-05 12:00:00', self::TZ);

        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $prayerTime, $now);

        $this->assertNotNull($window);
        $this->assertSame('12:25:00', $window['start']->format('H:i:s'));
        $this->assertSame('12:45:00', $window['end']->format('H:i:s'));
        $this->assertSame('12:45:00', $window['jamaat']->format('H:i:s'));
    }

    public function test_poster_and_countdown_share_the_same_resolved_iqamah_time(): void
    {
        $this->seedPosterSchedule(minutesBefore: 20, jamaat: '13:15:00');

        $prayerTime = PrayerTime::query()->first();
        $atJamaatCountdown = Carbon::parse('2026-07-05 13:14:30', self::TZ);

        $resolvedJamaat = PrayerJamaatTime::resolve($prayerTime, 'zohar', $atJamaatCountdown);
        $countdown = $this->service->getCountdownInfo($atJamaatCountdown);

        $this->assertNotNull($resolvedJamaat);
        $this->assertNotNull($countdown);
        $this->assertSame(
            $resolvedJamaat->toIso8601String(),
            $countdown['iqamah_time']->toIso8601String()
        );
    }

    public function test_five_minutes_before_poster_has_non_zero_window_until_jamaat(): void
    {
        $this->seedPosterSchedule(minutesBefore: 5, jamaat: '13:15:00');

        $schedule = MediaSchedule::query()->first();
        $prayerTime = PrayerTime::query()->first();
        $reference = Carbon::parse('2026-07-05 12:00:00', self::TZ);
        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $prayerTime, $reference);

        $this->assertNotNull($window);
        $this->assertSame('13:10:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:15:00', $window['end']->format('H:i:s'));

        Carbon::setTestNow(Carbon::parse('2026-07-05 13:10:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia(), 'Poster must start at jamaat - 5 minutes');

        Carbon::setTestNow(Carbon::parse('2026-07-05 13:12:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia(), 'Poster must stay active inside the 5-minute window');

        Carbon::setTestNow(Carbon::parse('2026-07-05 13:15:01', self::TZ));
        $this->assertNull($this->service->getCurrentMedia(), 'Poster must stop after jamaat');
    }

    public function test_after_prayer_poster_window_uses_mosque_timezone_even_if_app_timezone_is_utc(): void
    {
        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
        Setting::set('timezone', self::TZ);

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_after_prayer',
            'prayer_name' => 'zohar',
            'minutes_after_prayer' => 5,
            'is_active' => true,
        ]);

        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => '12:30:00',
            'zohar_jamaat' => '13:15:00',
        ]);

        $media = Media::query()->create([
            'title' => 'After Poster',
            'file_path' => 'media/after.png',
            'type' => 'image',
            'is_active' => true,
            'display_duration' => 1,
        ]);

        $schedule->mediaItems()->attach($media->id, [
            'duration' => 1,
            'priority' => 1,
            'is_active' => true,
            'gap_duration' => 0,
        ]);

        // 13:20 London == 12:20 UTC in July (BST)
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:20:00', 'UTC'));
        $this->assertNotNull(
            $this->service->getCurrentMedia(),
            '5 minutes after jamaat must resolve using Europe/London, not server UTC'
        );

        Carbon::setTestNow(Carbon::parse('2026-07-05 12:19:00', 'UTC'));
        $this->assertNull($this->service->getCurrentMedia(), 'Too early in mosque time');
    }

    private function seedPosterSchedule(
        int $minutesBefore,
        ?string $jamaat,
        ?string $beginning = '12:30:00'
    ): void {
        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => $beginning,
            'zohar_jamaat' => $jamaat,
        ]);

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => $minutesBefore,
            'is_active' => true,
        ]);

        $media = Media::query()->create([
            'title' => 'Timing Poster',
            'file_path' => 'media/test.png',
            'type' => 'image',
            'is_active' => true,
            'display_duration' => 1,
        ]);

        $schedule->mediaItems()->attach($media->id, [
            'duration' => 1,
            'priority' => 1,
            'is_active' => true,
            'gap_duration' => 0,
        ]);
    }
}
