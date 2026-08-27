<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Services\MediaDisplayService;
use App\Support\MosqueTimezone;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Full audit of prayer-relative poster scheduling against mosque-admin scenarios.
 * Reference time is always JAMAAT (never Adhan). Timezone is always the mosque setting.
 */
class PrayerRelativePosterAuditTest extends TestCase
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
            $table->time('zohar_adhan')->nullable();
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

        // Simulate a misconfigured production host (UTC) with mosque TZ in settings.
        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
        Setting::set('timezone', self::TZ);
        MosqueTimezone::apply(self::TZ);

        $this->service = app(MediaDisplayService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_reference_is_jamaat_not_adhan_when_they_differ(): void
    {
        $row = $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:15:00', beginning: '12:50:00');
        $resolved = PrayerJamaatTime::resolve($row, 'zohar');

        $this->assertSame('13:15:00', $resolved->format('H:i:s'));
        $this->assertSame(self::TZ, $resolved->timezoneName);
    }

    public function test_reference_is_jamaat_when_adhan_equals_jamaat(): void
    {
        $row = $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:00:00', beginning: '13:00:00');
        $resolved = PrayerJamaatTime::resolve($row, 'zohar');

        $this->assertSame('13:00:00', $resolved->format('H:i:s'));
    }

    public function test_changing_adhan_alone_does_not_move_poster_window(): void
    {
        $row = $this->seedPrayerTimes(adhan: '12:00:00', jamaat: '13:15:00');
        $schedule = $this->seedBeforeSchedule(5);
        $windowA = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $row);

        $row->zohar_adhan = '13:00:00';
        $row->save();
        $row->refresh();
        $windowB = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $row);

        $this->assertSame($windowA['start']->toIso8601String(), $windowB['start']->toIso8601String());
        $this->assertSame($windowA['end']->toIso8601String(), $windowB['end']->toIso8601String());
        $this->assertSame('13:10:00', $windowB['start']->format('H:i:s'));
        $this->assertSame('13:15:00', $windowB['end']->format('H:i:s'));
    }

    /** Scenario A — Adhan = Jamaat = 13:00, poster 5 minutes before */
    public function test_scenario_a_adhan_equals_jamaat_five_minutes_before(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:00:00');
        $this->seedBeforeSchedule(5);

        $window = MediaSchedule::query()->first()->resolveBeforePrayerWindow(
            Carbon::parse($this->date . ' 12:00:00', self::TZ)
        );

        $this->assertNotNull($window);
        $this->assertSame('jamaat', $window['reference']);
        $this->assertSame('12:55:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:00:00', $window['end']->format('H:i:s'));
        $this->assertTrue($window['start']->lt($window['end']));

        Carbon::setTestNow(Carbon::parse($this->date . ' 12:54:59', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 12:55:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 12:59:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:00:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:00:01', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());
    }

    /** Scenario B — Adhan 13:00, Jamaat 13:15, poster 5 minutes before Jamaat */
    public function test_scenario_b_adhan_before_jamaat_five_minutes_before(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:15:00');
        $this->seedBeforeSchedule(5);

        $window = MediaSchedule::query()->first()->resolveBeforePrayerWindow(
            Carbon::parse($this->date . ' 12:00:00', self::TZ)
        );

        $this->assertSame('13:10:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:15:00', $window['end']->format('H:i:s'));

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:09:59', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:10:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:14:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:15:01', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());
    }

    /** Scenario C — 10 minutes after Jamaat 13:15 → start 13:25 for 10 minutes */
    public function test_scenario_c_ten_minutes_after_jamaat(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:15:00');
        $this->seedAfterSchedule(10);

        $window = MediaSchedule::query()->first()->resolveAfterPrayerWindow(
            Carbon::parse($this->date . ' 12:00:00', self::TZ)
        );

        $this->assertSame('13:25:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:35:00', $window['end']->format('H:i:s'));
        $this->assertSame(PrayerJamaatTime::AFTER_POSTER_WINDOW_MINUTES, 10);

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:24:59', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:25:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:34:00', self::TZ));
        $this->assertNotNull($this->service->getCurrentMedia());

        Carbon::setTestNow(Carbon::parse($this->date . ' 13:35:01', self::TZ));
        $this->assertNull($this->service->getCurrentMedia());
    }

    public static function beforeMinuteProvider(): array
    {
        return [
            '1 minute before' => [1, '13:14:00', '13:15:00'],
            '2 minutes before' => [2, '13:13:00', '13:15:00'],
            '5 minutes before' => [5, '13:10:00', '13:15:00'],
            '10 minutes before' => [10, '13:05:00', '13:15:00'],
            '15 minutes before' => [15, '13:00:00', '13:15:00'],
        ];
    }

    #[DataProvider('beforeMinuteProvider')]
    public function test_before_prayer_windows_are_always_valid(int $minutes, string $start, string $end): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        $this->seedPrayerTimes(adhan: '12:45:00', jamaat: '13:15:00');
        $schedule = $this->seedBeforeSchedule($minutes);
        $row = PrayerTime::query()->first();

        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $row);

        $this->assertNotNull($window);
        $this->assertSame($start, $window['start']->format('H:i:s'));
        $this->assertSame($end, $window['end']->format('H:i:s'));
        $this->assertTrue(
            $window['start']->lt($window['end']),
            "window_start must be before window_end for {$minutes} minutes before"
        );

        $mid = $window['start']->copy()->addSeconds(15);
        // Stay clear of the fixed iqamah countdown [jamaat-30s, jamaat).
        if ($mid->gte($window['end']->copy()->subSeconds(30))) {
            $mid = $window['start']->copy()->addSecond();
        }

        Carbon::setTestNow($mid);
        $this->assertNotNull(
            $this->service->getCurrentMedia(),
            "Poster should be active mid-window for {$minutes} minutes before"
        );
    }

    public static function afterMinuteProvider(): array
    {
        return [
            '5 minutes after' => [5, '13:20:00', '13:30:00'],
            '10 minutes after' => [10, '13:25:00', '13:35:00'],
            '15 minutes after' => [15, '13:30:00', '13:40:00'],
        ];
    }

    #[DataProvider('afterMinuteProvider')]
    public function test_after_prayer_windows_are_always_valid(int $minutes, string $start, string $end): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:15:00');
        $schedule = $this->seedAfterSchedule($minutes);
        $row = PrayerTime::query()->first();

        $window = PrayerJamaatTime::afterPrayerPosterWindow($schedule, $row);

        $this->assertNotNull($window);
        $this->assertSame($start, $window['start']->format('H:i:s'));
        $this->assertSame($end, $window['end']->format('H:i:s'));
        $this->assertTrue($window['start']->lt($window['end']));

        Carbon::setTestNow($window['start']->copy()->addMinutes(1));
        $this->assertNotNull($this->service->getCurrentMedia());
    }

    public function test_production_style_utc_server_still_uses_mosque_timezone_for_scenario_a(): void
    {
        // Host misconfigured to UTC; mosque setting remains Europe/London (do not re-apply).
        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');

        $this->assertSame(self::TZ, PrayerJamaatTime::appTimezone());
        $this->assertSame('UTC', date_default_timezone_get());

        $this->seedPrayerTimes(adhan: '13:00:00', jamaat: '13:00:00');
        $this->seedBeforeSchedule(5);

        // 12:55 London in July (BST) == 11:55 UTC
        Carbon::setTestNow(Carbon::parse($this->date . ' 11:55:00', 'UTC'));
        $this->assertNotNull(
            $this->service->getCurrentMedia(),
            'Must activate on UTC hosts when mosque-local time is inside the window'
        );

        $diagnostic = $this->service->getPosterScheduleDiagnostic();
        $this->assertSame(self::TZ, $diagnostic['mosque_timezone']);
    }

    private function seedPrayerTimes(string $adhan, string $jamaat, string $beginning = '12:30:00'): PrayerTime
    {
        return PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => $beginning,
            'zohar_adhan' => $adhan,
            'zohar_jamaat' => $jamaat,
        ]);
    }

    private function seedBeforeSchedule(int $minutes): MediaSchedule
    {
        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => $minutes,
            'is_active' => true,
        ]);

        $this->attachMedia($schedule);

        return $schedule;
    }

    private function seedAfterSchedule(int $minutes): MediaSchedule
    {
        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_after_prayer',
            'prayer_name' => 'zohar',
            'minutes_after_prayer' => $minutes,
            'is_active' => true,
        ]);

        $this->attachMedia($schedule);

        return $schedule;
    }

    private function attachMedia(MediaSchedule $schedule): void
    {
        $media = Media::query()->create([
            'title' => 'Audit Poster',
            'file_path' => 'media/audit.png',
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
