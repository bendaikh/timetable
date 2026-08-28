<?php

namespace Tests\Unit;

use App\Models\MediaSchedule;
use App\Support\ScheduleDaysOfWeek;
use Carbon\Carbon;
use Tests\TestCase;

class ScheduleDaysOfWeekTest extends TestCase
{
    public function test_normalize_converts_string_json_and_mixed_types(): void
    {
        $this->assertSame([7], ScheduleDaysOfWeek::normalize('["7"]'));
        $this->assertSame([1, 7], ScheduleDaysOfWeek::normalize(['1', 7, '7']));
        $this->assertNull(ScheduleDaysOfWeek::normalize([]));
        $this->assertNull(ScheduleDaysOfWeek::normalize(null));
    }

    public function test_is_active_today_matches_sunday_with_string_storage(): void
    {
        $sunday = Carbon::parse('2026-08-30 12:00:00', 'Europe/London'); // ISO Sunday = 7
        $monday = Carbon::parse('2026-08-31 12:00:00', 'Europe/London');

        $this->assertTrue(ScheduleDaysOfWeek::isActiveToday('["7"]', $sunday));
        $this->assertFalse(ScheduleDaysOfWeek::isActiveToday('["7"]', $monday));
        $this->assertTrue(ScheduleDaysOfWeek::isActiveToday(null, $monday));
    }

    public function test_media_schedule_is_active_for_today_uses_normalized_days(): void
    {
        $schedule = new MediaSchedule([
            'is_active' => true,
            'days_of_week' => ['7'],
        ]);

        $sunday = Carbon::parse('2026-08-30 12:00:00', 'Europe/London');
        $monday = Carbon::parse('2026-08-31 12:00:00', 'Europe/London');

        $this->assertTrue($schedule->isActiveForToday($sunday));
        $this->assertFalse($schedule->isActiveForToday($monday));
    }
}
