<?php

namespace Tests\Unit;

use App\Support\PrayerCountdownWindows;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PrayerCountdownTest extends TestCase
{
    private Carbon $iqamah;

    private Carbon $adhan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iqamah = Carbon::parse('2026-06-24 15:00:00');
        $this->adhan = Carbon::parse('2026-06-24 12:37:00');
    }

    public function test_adhan_countdown_starts_thirty_seconds_before_adhan_column_time(): void
    {
        $now = $this->adhan->copy()->subSeconds(30);

        $phase = PrayerCountdownWindows::resolveAdhanPopupPhase($now, $this->adhan, $this->iqamah);
        $info = PrayerCountdownWindows::buildPayload('zohar', $phase, $now, $this->iqamah, $this->adhan);

        $this->assertSame('adhan', $phase);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('Adhan will start in 30 seconds', $info['message']);
        $this->assertSame('adhan', $info['target_field']);
        $this->assertTrue($info['countdown_end']->equalTo($this->adhan));
    }

    public function test_adhan_countdown_inactive_one_second_before_trigger(): void
    {
        $now = $this->adhan->copy()->subSeconds(31);

        $this->assertNull(PrayerCountdownWindows::resolveAdhanPopupPhase($now, $this->adhan, $this->iqamah));
    }

    public function test_no_fixed_twenty_minute_adhan_window_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1200);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah, $this->adhan));
    }

    public function test_iqamah_countdown_starts_thirty_seconds_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(30);

        $phase = PrayerCountdownWindows::resolveJamaatPopupPhase($now, $this->iqamah);
        $info = PrayerCountdownWindows::buildPayload('zohar', $phase, $now, $this->iqamah, $this->adhan);

        $this->assertSame('iqamah', $phase);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('Iqamah will start in 30 seconds', $info['message']);
        $this->assertTrue($info['countdown_end']->equalTo($this->iqamah));
    }

    public function test_iqamah_countdown_reaches_one_second_remaining(): void
    {
        $now = $this->iqamah->copy()->subSecond();
        $info = PrayerCountdownWindows::buildPayload(
            'zohar',
            PrayerCountdownWindows::resolveJamaatPopupPhase($now, $this->iqamah),
            $now,
            $this->iqamah,
            $this->adhan
        );

        $this->assertSame(1, $info['seconds_remaining']);
    }

    public function test_no_countdown_at_exact_jamaat_time(): void
    {
        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($this->iqamah, $this->iqamah, $this->adhan));
    }

    public function test_no_countdown_after_jamaat(): void
    {
        $now = $this->iqamah->copy()->addSecond();

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah, $this->adhan));
    }

    public function test_gap_has_no_popup_between_adhan_and_jamaat_countdowns(): void
    {
        $this->assertNull(PrayerCountdownWindows::resolveActivePhase(
            $this->adhan->copy()->addMinute(),
            $this->iqamah,
            $this->adhan
        ));
        $this->assertNull(PrayerCountdownWindows::resolveActivePhase(
            $this->iqamah->copy()->subSeconds(31),
            $this->iqamah,
            $this->adhan
        ));
    }

    public function test_iqamah_phase_takes_priority_over_adhan_when_both_cannot_overlap(): void
    {
        $now = $this->iqamah->copy()->subSeconds(15);

        $this->assertSame('iqamah', PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah, $this->adhan));
    }

    public function test_adhan_equals_jamaat_skips_adhan_popup(): void
    {
        $same = Carbon::parse('2026-06-24 19:00:00');
        $now = $same->copy()->subSeconds(30);

        $this->assertNull(PrayerCountdownWindows::resolveAdhanPopupPhase($now, $same, $same));
        $this->assertSame('iqamah', PrayerCountdownWindows::resolveJamaatPopupPhase($now, $same));
        $this->assertNull(PrayerCountdownWindows::windowSchedule($same, $same)['adhan_countdown']);
    }

    public function test_window_schedule_uses_adhan_and_jamaat_columns(): void
    {
        $schedule = PrayerCountdownWindows::windowSchedule($this->iqamah, $this->adhan);

        $this->assertSame('12:36:30', Carbon::parse($schedule['adhan_countdown']['start'])->format('H:i:s'));
        $this->assertSame('12:37:00', Carbon::parse($schedule['adhan_countdown']['end'])->format('H:i:s'));
        $this->assertSame('14:59:30', Carbon::parse($schedule['iqamah_countdown']['start'])->format('H:i:s'));
        $this->assertSame('15:00:00', Carbon::parse($schedule['iqamah_countdown']['end'])->format('H:i:s'));
        $this->assertSame('adhan', $schedule['adhan_countdown']['target_field']);
        $this->assertSame('jamaat', $schedule['iqamah_countdown']['target_field']);
    }

    public function test_countdown_duration_is_always_thirty_seconds(): void
    {
        $this->assertSame(30, PrayerCountdownWindows::DURATION_SECONDS);
        $this->assertSame(30, PrayerCountdownWindows::IQAMAH_LEAD_SECONDS);
    }

    public function test_adhan_timer_counts_down_to_zero(): void
    {
        $start = $this->adhan->copy()->subSeconds(30);
        for ($elapsed = 0; $elapsed <= 30; $elapsed++) {
            $now = $start->copy()->addSeconds($elapsed);
            $phase = PrayerCountdownWindows::resolveAdhanPopupPhase($now, $this->adhan, $this->iqamah);
            if ($elapsed < 30) {
                $this->assertSame('adhan', $phase);
                $info = PrayerCountdownWindows::buildPayload('zohar', $phase, $now, $this->iqamah, $this->adhan);
                $this->assertSame(30 - $elapsed, $info['seconds_remaining']);
            } else {
                $this->assertNull($phase);
            }
        }
    }

    public function test_iqamah_timer_counts_down_to_zero(): void
    {
        $start = $this->iqamah->copy()->subSeconds(30);
        for ($elapsed = 0; $elapsed <= 30; $elapsed++) {
            $now = $start->copy()->addSeconds($elapsed);
            $phase = PrayerCountdownWindows::resolveJamaatPopupPhase($now, $this->iqamah);
            if ($elapsed < 30) {
                $this->assertSame('iqamah', $phase);
                $info = PrayerCountdownWindows::buildPayload('zohar', $phase, $now, $this->iqamah, $this->adhan);
                $this->assertSame(30 - $elapsed, $info['seconds_remaining']);
            } else {
                $this->assertNull($phase);
            }
        }
    }
}
