<?php

namespace Tests\Unit;

use App\Models\PrayerTime;
use App\Models\Setting;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * "Next Prayer" is next JAMAAT (iqamah), consistent across TV, API, and admin.
 */
class NextPrayerConsistencyTest extends TestCase
{
    private const TZ = 'Europe/London';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => self::TZ]);

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

        Setting::set('fajr_jamaat_offset', '10', 'integer');
        Setting::set('zohar_jamaat_offset', '15', 'integer');
        Setting::set('asr_jamaat_offset', '20', 'integer');
        Setting::set('maghrib_jamaat_offset', '0', 'integer');
        Setting::set('isha_jamaat_offset', '10', 'integer');
        Setting::set('timetable_min_date', '2026-07-05', 'string');
        Setting::set('timetable_max_date', '2026-07-06', 'string');
    }

    public function test_next_prayer_uses_explicit_jamaat_not_beginning(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', self::TZ));

        PrayerTime::create([
            'date' => '2026-07-05',
            'fajr' => '03:30:00',
            'fajr_jamaat' => '04:00:00',
            'zohar' => '13:00:00',
            'zohar_jamaat' => '13:30:00',
            'asr' => '17:00:00',
            'asr_jamaat' => '17:30:00',
            'maghrib' => '21:00:00',
            'maghrib_jamaat' => '21:00:00',
            'isha' => '22:30:00',
            'isha_jamaat' => '22:45:00',
        ]);

        $next = PrayerTime::getNextPrayer();
        $today = PrayerTime::getTodayPrayerTimes();
        $expectedJamaat = PrayerJamaatTime::resolve($today, 'zohar');

        $this->assertNotNull($next);
        $this->assertSame('zohar', $next['name']);
        $this->assertSame('jamaat', $next['reference']);
        $this->assertSame($expectedJamaat->format('H:i:s'), $next['time']);
        // Beginning is 13:00; jamaat is 13:30 — must not return beginning.
        $this->assertNotSame('13:00:00', $next['time']);
        $this->assertSame(90 * 60, (int) $next['time_until']);
    }

    public function test_next_prayer_falls_back_to_beginning_plus_offset(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', self::TZ));

        PrayerTime::create([
            'date' => '2026-07-05',
            'fajr' => '03:30:00',
            'zohar' => '13:00:00',
            'asr' => '17:00:00',
            'maghrib' => '21:00:00',
            'isha' => '22:30:00',
        ]);

        $next = PrayerTime::getNextPrayer();

        $this->assertNotNull($next);
        $this->assertSame('zohar', $next['name']);
        // Offset 15 → 13:15 jamaat
        $this->assertSame('13:15:00', $next['time']);
        $this->assertSame('jamaat', $next['reference']);
    }

    public function test_after_isha_next_prayer_is_tomorrow_fajr_jamaat(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 23:00:00', self::TZ));

        PrayerTime::create([
            'date' => '2026-07-05',
            'fajr' => '03:30:00',
            'fajr_jamaat' => '04:00:00',
            'zohar' => '13:00:00',
            'zohar_jamaat' => '13:30:00',
            'asr' => '17:00:00',
            'asr_jamaat' => '17:30:00',
            'maghrib' => '21:00:00',
            'maghrib_jamaat' => '21:00:00',
            'isha' => '22:30:00',
            'isha_jamaat' => '22:45:00',
        ]);

        PrayerTime::create([
            'date' => '2026-07-06',
            'fajr' => '03:31:00',
            'fajr_jamaat' => '04:05:00',
            'zohar' => '13:01:00',
            'asr' => '17:01:00',
            'maghrib' => '21:01:00',
            'isha' => '22:31:00',
        ]);

        $next = PrayerTime::getNextPrayer();

        $this->assertNotNull($next);
        $this->assertSame('fajr', $next['name']);
        $this->assertSame('04:05:00', $next['time']);
        $this->assertSame('jamaat', $next['reference']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
