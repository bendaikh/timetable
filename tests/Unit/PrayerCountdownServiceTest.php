<?php

namespace Tests\Unit;

use App\Models\PrayerTime;
use App\Models\Setting;
use App\Services\MediaDisplayService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrayerCountdownServiceTest extends TestCase
{
    private const TZ = 'Europe/London';

    private MediaDisplayService $service;

    private string $date = '2026-06-24';

    protected function setUp(): void
    {
        parent::setUp();

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
            $table->time('fajr')->nullable();
            $table->time('fajr_adhan')->nullable();
            $table->time('fajr_jamaat')->nullable();
            $table->time('zohar')->nullable();
            $table->time('zohar_adhan')->nullable();
            $table->time('zohar_jamaat')->nullable();
            $table->time('asr')->nullable();
            $table->time('asr_adhan')->nullable();
            $table->time('asr_jamaat')->nullable();
            $table->time('maghrib')->nullable();
            $table->time('maghrib_adhan')->nullable();
            $table->time('maghrib_jamaat')->nullable();
            $table->time('isha')->nullable();
            $table->time('isha_adhan')->nullable();
            $table->time('isha_jamaat')->nullable();
            $table->timestamps();
        });

        Setting::set('timezone', self::TZ);

        PrayerTime::query()->create([
            'date' => $this->date,
            'fajr' => '05:00:00',
            'fajr_jamaat' => '05:10:00',
            'zohar' => '12:00:00',
            'zohar_adhan' => '12:37:00',
            'zohar_jamaat' => '15:00:00',
            'asr' => '16:00:00',
            'asr_jamaat' => '16:15:00',
            'maghrib' => '19:00:00',
            'maghrib_adhan' => '19:05:00',
            'maghrib_jamaat' => '19:10:00',
            'isha' => '20:30:00',
            'isha_jamaat' => '20:45:00',
        ]);

        $this->service = app(MediaDisplayService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function at(string $time): ?array
    {
        $now = Carbon::parse("{$this->date} {$time}", self::TZ);

        return $this->service->getCountdownInfo($now);
    }

    public function test_service_countdown_one_at_twenty_minutes_before_jamaat(): void
    {
        $info = $this->at('14:40:00');

        $this->assertNotNull($info);
        $this->assertSame('adhan', $info['phase']);
        $this->assertSame('Adhan will start in 30 seconds', $info['message']);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('jamaat', $info['target_field']);
    }

    public function test_service_inactive_one_second_before_countdown_one(): void
    {
        $this->assertNull($this->at('14:39:59'));
    }

    public function test_service_gap_between_countdowns(): void
    {
        $this->assertNull($this->at('14:41:00'));
        $this->assertNull($this->at('14:59:29'));
    }

    public function test_service_countdown_two_at_thirty_seconds_before_jamaat(): void
    {
        $info = $this->at('14:59:30');

        $this->assertNotNull($info);
        $this->assertSame('iqamah', $info['phase']);
        $this->assertSame('Iqamah will start in 30 seconds', $info['message']);
        $this->assertSame(30, $info['seconds_remaining']);
    }

    public function test_service_inactive_at_and_after_jamaat(): void
    {
        $this->assertNull($this->at('15:00:00'));
        $this->assertNull($this->at('15:00:01'));
    }

    public function test_service_negative_times(): void
    {
        foreach (['14:37:00', '14:39:00', '14:41:00', '14:59:29', '15:01:00'] as $time) {
            $this->assertNull($this->at($time), "Expected inactive at {$time}");
        }
    }

    public function test_beginning_and_adhan_changes_do_not_move_countdown_windows(): void
    {
        PrayerTime::query()->whereDate('date', $this->date)->first()->update([
            'zohar' => '11:00:00',
            'zohar_adhan' => '11:30:00',
        ]);

        $info = $this->at('14:40:00');

        $this->assertSame('adhan', $info['phase']);
        $this->assertTrue($info['iqamah_time']->equalTo(Carbon::parse("{$this->date} 15:00:00", self::TZ)));
    }

    public function test_service_uses_europe_london_timezone(): void
    {
        $this->assertSame(self::TZ, $this->service->getAppTimezone());

        $now = Carbon::parse("{$this->date} 14:40:00", self::TZ);
        $info = $this->service->getCountdownInfo($now);

        $this->assertNotNull($info);
        $this->assertSame('adhan', $info['phase']);
    }

    public function test_diagnostic_reports_active_countdown(): void
    {
        $now = Carbon::parse("{$this->date} 14:40:00", self::TZ);
        $diagnostic = $this->service->getCountdownDiagnostic($now);

        $this->assertTrue($diagnostic['log']['countdown_active']);
        $this->assertSame('adhan', $diagnostic['log']['countdown_phase']);
        $this->assertSame(self::TZ, $diagnostic['log']['server_timezone']);
    }
}
