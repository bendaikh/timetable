<?php

namespace Tests\Unit;

use App\Models\PrayerTime;
use App\Support\PrayerTimeDisplayRollover;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrayerTimeDisplayRolloverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.timezone' => 'Europe/London']);

        Schema::dropIfExists('prayer_times');
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
            $table->time('sun_rise')->nullable();
            $table->timestamps();
        });
    }

    private function seedDay(string $date, array $times): PrayerTime
    {
        return PrayerTime::create(array_merge(['date' => $date], $times));
    }

    public function test_prayer_row_stays_on_today_before_jamaat(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'fajr' => '04:30:00',
            'fajr_jamaat' => '04:40:00',
            'zohar' => '13:00:00',
            'zohar_jamaat' => '13:15:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'fajr_jamaat' => '04:42:00',
            'zohar' => '13:01:00',
            'zohar_jamaat' => '13:16:00',
        ]);

        $now = Carbon::parse('2026-08-23 04:00:00', 'Europe/London');
        $row = PrayerTimeDisplayRollover::resolvePrayerRow($today, $tomorrow, 'fajr', $now);

        $this->assertSame('04:30', $row['beginning']);
        $this->assertSame('04:40', $row['jamaat']);
    }

    public function test_prayer_row_rolls_after_jamaat_even_if_before_next_prayer(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'fajr' => '04:30:00',
            'fajr_jamaat' => '04:40:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'fajr_jamaat' => '04:42:00',
        ]);

        $now = Carbon::parse('2026-08-23 10:00:00', 'Europe/London');
        $row = PrayerTimeDisplayRollover::resolvePrayerRow($today, $tomorrow, 'fajr', $now);

        $this->assertSame('04:32', $row['beginning']);
        $this->assertSame('04:42', $row['jamaat']);
    }

    public function test_prayer_row_rolls_to_tomorrow_after_jamaat(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'fajr' => '04:30:00',
            'fajr_jamaat' => '04:40:00',
            'fajr_adhan' => '04:35:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'fajr_jamaat' => '04:42:00',
            'fajr_adhan' => '04:37:00',
        ]);

        $now = Carbon::parse('2026-08-23 05:00:00', 'Europe/London');
        $row = PrayerTimeDisplayRollover::resolvePrayerRow($today, $tomorrow, 'fajr', $now);

        $this->assertSame('04:32', $row['beginning']);
        $this->assertSame('04:42', $row['jamaat']);
        $this->assertSame('04:37', $row['adhan']);
    }

    public function test_all_prayers_roll_independently(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'fajr' => '04:30:00',
            'fajr_jamaat' => '04:40:00',
            'zohar' => '13:00:00',
            'zohar_jamaat' => '13:15:00',
            'asr' => '16:30:00',
            'asr_jamaat' => '16:45:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'fajr_jamaat' => '04:42:00',
            'zohar' => '13:01:00',
            'zohar_jamaat' => '13:16:00',
            'asr' => '16:31:00',
            'asr_jamaat' => '16:46:00',
        ]);

        $now = Carbon::parse('2026-08-23 14:00:00', 'Europe/London');
        $rows = PrayerTimeDisplayRollover::resolveAllPrayerRows($today, $tomorrow, $now);

        $this->assertSame('04:42', $rows['fajr']['jamaat']);
        $this->assertSame('13:16', $rows['zohar']['jamaat']);
        $this->assertSame('16:45', $rows['asr']['jamaat']);
    }

    public function test_late_night_isha_rolls_to_tomorrow(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'isha' => '20:30:00',
            'isha_jamaat' => '20:45:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'fajr_jamaat' => '04:42:00',
            'isha' => '20:31:00',
            'isha_jamaat' => '20:46:00',
        ]);

        $now = Carbon::parse('2026-08-23 22:00:00', 'Europe/London');
        $isha = PrayerTimeDisplayRollover::resolvePrayerRow($today, $tomorrow, 'isha', $now);

        $this->assertSame('20:46', $isha['jamaat']);
    }

    public function test_special_times_roll_with_linked_prayer_or_own_clock(): void
    {
        $today = $this->seedDay('2026-08-23', [
            'fajr' => '04:30:00',
            'fajr_jamaat' => '04:40:00',
            'zohar' => '13:00:00',
            'zohar_jamaat' => '13:15:00',
            'sun_rise' => '06:00:00',
        ]);
        $tomorrow = $this->seedDay('2026-08-24', [
            'fajr' => '04:32:00',
            'zohar' => '13:01:00',
            'sun_rise' => '06:01:00',
        ]);

        $now = Carbon::parse('2026-08-23 14:00:00', 'Europe/London');
        $special = PrayerTimeDisplayRollover::resolveSpecialTimes($today, $tomorrow, $now);

        $this->assertSame('04:32', $special['sehri_ends']);
        $this->assertSame('06:01', $special['sun_rise']);
        $this->assertSame('13:01', $special['noon']);
    }
}
