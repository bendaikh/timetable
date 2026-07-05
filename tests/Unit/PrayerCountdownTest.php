<?php

namespace Tests\Unit;

use App\Support\PrayerCountdownWindows;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class PrayerCountdownTest extends TestCase
{
    private Carbon $iqamah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iqamah = Carbon::parse('2026-06-24 15:00:00');
    }

    public function test_countdown_one_starts_exactly_twenty_minutes_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1200);

        $phase = PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah);
        $info = PrayerCountdownWindows::buildPayload('zohar', $this->iqamah, $phase, $now);

        $this->assertSame('adhan', $phase);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('Adhan will start in 30 seconds', $info['message']);
        $this->assertSame('jamaat', $info['target_field']);
        $this->assertTrue($info['countdown_start']->equalTo($now));
        $this->assertTrue($info['countdown_end']->equalTo($now->copy()->addSeconds(30)));
    }

    public function test_countdown_one_inactive_one_second_before_trigger(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1201);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_countdown_one_active_at_exact_trigger_millisecond(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1200);

        $this->assertSame('adhan', PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_countdown_one_ends_after_exactly_thirty_seconds(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1170);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_countdown_two_starts_exactly_thirty_seconds_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(30);

        $phase = PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah);
        $info = PrayerCountdownWindows::buildPayload('zohar', $this->iqamah, $phase, $now);

        $this->assertSame('iqamah', $phase);
        $this->assertSame(30, $info['seconds_remaining']);
        $this->assertSame('Iqamah will start in 30 seconds', $info['message']);
        $this->assertTrue($info['countdown_end']->equalTo($this->iqamah));
    }

    public function test_countdown_two_reaches_one_second_remaining(): void
    {
        $now = $this->iqamah->copy()->subSecond();
        $info = PrayerCountdownWindows::buildPayload(
            'zohar',
            $this->iqamah,
            PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah),
            $now
        );

        $this->assertSame(1, $info['seconds_remaining']);
    }

    public function test_no_countdown_at_exact_jamaat_time(): void
    {
        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($this->iqamah, $this->iqamah));
    }

    public function test_no_countdown_after_jamaat(): void
    {
        $now = $this->iqamah->copy()->addSecond();

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_gap_has_no_popup_from_nineteen_minutes_twenty_nine_seconds_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1169);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_gap_has_no_popup_at_thirty_one_seconds_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(31);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_gap_has_no_popup_throughout_entire_middle_period(): void
    {
        for ($secondsBefore = 1169; $secondsBefore >= 31; $secondsBefore--) {
            $now = $this->iqamah->copy()->subSeconds($secondsBefore);
            $this->assertNull(
                PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah),
                "Expected no popup at {$secondsBefore}s before jamaat"
            );
        }
    }

    public function test_negative_no_popup_at_twenty_three_minutes_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1380);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_negative_no_popup_at_twenty_one_minutes_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1260);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_negative_no_popup_at_nineteen_minutes_before_jamaat(): void
    {
        $now = $this->iqamah->copy()->subSeconds(1140);

        $this->assertNull(PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_iqamah_phase_takes_priority_when_both_windows_cannot_overlap(): void
    {
        $now = $this->iqamah->copy()->subSeconds(15);

        $this->assertSame('iqamah', PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah));
    }

    public function test_window_schedule_uses_jamaat_only(): void
    {
        $schedule = PrayerCountdownWindows::windowSchedule($this->iqamah);

        $this->assertSame('14:40:00', Carbon::parse($schedule['adhan_countdown']['start'])->format('H:i:s'));
        $this->assertSame('14:40:30', Carbon::parse($schedule['adhan_countdown']['end'])->format('H:i:s'));
        $this->assertSame('14:59:30', Carbon::parse($schedule['iqamah_countdown']['start'])->format('H:i:s'));
        $this->assertSame('15:00:00', Carbon::parse($schedule['iqamah_countdown']['end'])->format('H:i:s'));
        $this->assertSame('jamaat', $schedule['adhan_countdown']['target_field']);
        $this->assertSame('jamaat', $schedule['iqamah_countdown']['target_field']);
    }

    public function test_countdown_duration_is_always_thirty_seconds(): void
    {
        $this->assertSame(30, PrayerCountdownWindows::DURATION_SECONDS);
        $this->assertSame(1200, PrayerCountdownWindows::ADHAN_LEAD_SECONDS);
        $this->assertSame(30, PrayerCountdownWindows::IQAMAH_LEAD_SECONDS);
    }

    public function test_countdown_one_timer_counts_down_to_zero(): void
    {
        $start = $this->iqamah->copy()->subSeconds(1200);
        for ($elapsed = 0; $elapsed <= 30; $elapsed++) {
            $now = $start->copy()->addSeconds($elapsed);
            $phase = PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah);
            if ($elapsed < 30) {
                $this->assertSame('adhan', $phase);
                $info = PrayerCountdownWindows::buildPayload('zohar', $this->iqamah, $phase, $now);
                $this->assertSame(30 - $elapsed, $info['seconds_remaining']);
            } else {
                $this->assertNull($phase);
            }
        }
    }

    public function test_countdown_two_timer_counts_down_to_zero(): void
    {
        $start = $this->iqamah->copy()->subSeconds(30);
        for ($elapsed = 0; $elapsed <= 30; $elapsed++) {
            $now = $start->copy()->addSeconds($elapsed);
            $phase = PrayerCountdownWindows::resolveActivePhase($now, $this->iqamah);
            if ($elapsed < 30) {
                $this->assertSame('iqamah', $phase);
                $info = PrayerCountdownWindows::buildPayload('zohar', $this->iqamah, $phase, $now);
                $this->assertSame(30 - $elapsed, $info['seconds_remaining']);
            } else {
                $this->assertNull($phase);
            }
        }
    }
}
