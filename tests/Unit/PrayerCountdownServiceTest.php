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

    public function test_service_adhan_countdown_at_uploaded_adhan_time(): void
    {
        $info = $this->at('12:36:30');

        $this->assertNotNull($info);
        $this->assertSame('adhan', $info['phase']);
        $this->assertSame('Adhan will start in 30 seconds', $info['message']);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('adhan', $info['target_field']);
        $this->assertTrue($info['target_time']->equalTo(Carbon::parse("{$this->date} 12:37:00", self::TZ)));
    }

    public function test_service_no_adhan_popup_twenty_minutes_before_jamaat(): void
    {
        $this->assertNull($this->at('14:40:00'));
    }

    public function test_service_inactive_one_second_before_adhan_countdown(): void
    {
        $this->assertNull($this->at('12:36:29'));
    }

    public function test_service_gap_between_adhan_and_jamaat_countdowns(): void
    {
        $this->assertNull($this->at('12:38:00'));
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

        $info = $this->at('14:59:30');

        $this->assertSame('iqamah', $info['phase']);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertTrue($info['iqamah_time']->equalTo(Carbon::parse("{$this->date} 15:00:00", self::TZ)));
    }

    public function test_service_uses_europe_london_timezone(): void
    {
        $this->assertSame(self::TZ, $this->service->getAppTimezone());

        $now = Carbon::parse("{$this->date} 14:59:30", self::TZ);
        $info = $this->service->getCountdownInfo($now);

        $this->assertNotNull($info);
        $this->assertSame('iqamah', $info['phase']);
    }

    public function test_diagnostic_reports_active_countdown(): void
    {
        $now = Carbon::parse("{$this->date} 14:59:30", self::TZ);
        $diagnostic = $this->service->getCountdownDiagnostic($now);

        $this->assertTrue($diagnostic['log']['countdown_active']);
        $this->assertSame('iqamah', $diagnostic['log']['countdown_phase']);
        $this->assertSame(self::TZ, $diagnostic['log']['server_timezone']);
    }

    public function test_get_current_media_returns_null_during_countdown(): void
    {
        $now = Carbon::parse("{$this->date} 14:59:30", self::TZ);
        Carbon::setTestNow($now);

        $this->assertTrue($this->service->isAdhanOrCountdownActive($now));
        $this->assertNull($this->service->getCurrentMedia());
    }

    public function test_fajr_maghrib_isha_popup_is_thirty_seconds_before_jamaat_only(): void
    {
        Setting::set('adhan_countdown_duration', '75');

        $cases = [
            ['prayer' => 'fajr', 'at' => '05:09:30', 'jamaat' => '05:10:00'],
            ['prayer' => 'maghrib', 'at' => '19:09:30', 'jamaat' => '19:10:00'],
            ['prayer' => 'isha', 'at' => '20:44:30', 'jamaat' => '20:45:00'],
        ];

        foreach ($cases as $case) {
            $info = $this->at($case['at']);

            $this->assertNotNull($info, "Expected popup for {$case['prayer']}");
            $this->assertSame('iqamah', $info['phase']);
            $this->assertSame(ucfirst($case['prayer']), $info['prayer_name']);
            $this->assertSame(30, $info['seconds_remaining']);
            $this->assertSame(30, $info['countdown_duration']);
            $this->assertTrue(
                $info['iqamah_time']->equalTo(Carbon::parse("{$this->date} {$case['jamaat']}", self::TZ))
            );
        }
    }

    public function test_maghrib_with_same_adhan_and_jamaat_shows_only_jamaat_popup(): void
    {
        PrayerTime::query()->whereDate('date', $this->date)->update([
            'maghrib' => '19:00:00',
            'maghrib_adhan' => '19:00:00',
            'maghrib_jamaat' => '19:00:00',
        ]);

        $this->assertNull($this->at('18:59:00'));
        $info = $this->at('18:59:30');
        $this->assertNotNull($info);
        $this->assertSame('iqamah', $info['phase']);
        $this->assertSame('Maghrib', $info['prayer_name']);
    }

    public function test_isha_with_same_adhan_and_jamaat_shows_only_jamaat_popup(): void
    {
        PrayerTime::query()->whereDate('date', $this->date)->update([
            'isha' => '20:30:00',
            'isha_adhan' => '20:30:00',
            'isha_jamaat' => '20:30:00',
        ]);

        $this->assertNull($this->at('20:28:30'));
        $info = $this->at('20:29:30');
        $this->assertNotNull($info);
        $this->assertSame('iqamah', $info['phase']);
        $this->assertSame('Isha', $info['prayer_name']);
    }
}
