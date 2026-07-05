<?php

namespace Tests\Unit;

use App\Support\ScheduledMediaWindow;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ScheduledMediaWindowTest extends TestCase
{
    private const TZ = 'Europe/London';

    public function test_null_start_and_end_are_always_eligible(): void
    {
        $pivot = (object) [
            'start_date' => null,
            'start_time' => null,
            'expiry_date' => null,
            'expiry_time' => null,
        ];

        $now = Carbon::parse('2026-07-05 12:00:00', self::TZ);

        $this->assertTrue(ScheduledMediaWindow::isEligible($pivot, $now, self::TZ));
    }

    public function test_media_is_ineligible_before_start_window(): void
    {
        $pivot = (object) [
            'start_date' => '2026-07-05',
            'start_time' => '13:00:00',
            'expiry_date' => '2026-07-05',
            'expiry_time' => '14:00:00',
        ];

        $before = Carbon::parse('2026-07-05 12:59:59', self::TZ);
        $afterStart = Carbon::parse('2026-07-05 13:00:00', self::TZ);

        $this->assertFalse(ScheduledMediaWindow::isEligible($pivot, $before, self::TZ));
        $this->assertTrue(ScheduledMediaWindow::isEligible($pivot, $afterStart, self::TZ));
    }

    public function test_media_is_ineligible_at_or_after_end_window(): void
    {
        $pivot = (object) [
            'start_date' => '2026-07-05',
            'start_time' => '13:00:00',
            'expiry_date' => '2026-07-05',
            'expiry_time' => '14:00:00',
        ];

        $beforeEnd = Carbon::parse('2026-07-05 13:59:59', self::TZ);
        $atEnd = Carbon::parse('2026-07-05 14:00:00', self::TZ);

        $this->assertTrue(ScheduledMediaWindow::isEligible($pivot, $beforeEnd, self::TZ));
        $this->assertFalse(ScheduledMediaWindow::isEligible($pivot, $atEnd, self::TZ));
    }

    public function test_validation_requires_start_and_end_pairs(): void
    {
        $this->expectException(ValidationException::class);

        ScheduledMediaWindow::validateRequestWindows(
            [10],
            ['2026-07-05'],
            [null],
            [null],
            [null],
            self::TZ
        );
    }

    public function test_validation_requires_start_before_end(): void
    {
        $this->expectException(ValidationException::class);

        ScheduledMediaWindow::validateRequestWindows(
            [10],
            ['2026-07-06'],
            ['10:00'],
            ['2026-07-05'],
            ['12:00'],
            self::TZ
        );
    }
}
