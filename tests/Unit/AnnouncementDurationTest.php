<?php

namespace Tests\Unit;

use App\Models\Announcement;
use PHPUnit\Framework\TestCase;

class AnnouncementDurationTest extends TestCase
{
    public function test_formatted_display_duration_shows_seconds_under_one_minute(): void
    {
        $announcement = new Announcement(['display_duration' => 30]);

        $this->assertSame('30s', $announcement->formattedDisplayDuration());
    }

    public function test_formatted_display_duration_distinguishes_different_second_values(): void
    {
        $thirty = new Announcement(['display_duration' => 30]);
        $five = new Announcement(['display_duration' => 5]);

        $this->assertNotSame($thirty->formattedDisplayDuration(), $five->formattedDisplayDuration());
        $this->assertSame('5s', $five->formattedDisplayDuration());
    }

    public function test_formatted_display_duration_shows_minutes_and_seconds(): void
    {
        $announcement = new Announcement(['display_duration' => 90]);

        $this->assertSame('1m 30s', $announcement->formattedDisplayDuration());
    }

    public function test_formatted_display_duration_shows_exact_minutes(): void
    {
        $announcement = new Announcement(['display_duration' => 120]);

        $this->assertSame('2m', $announcement->formattedDisplayDuration());
    }

    public function test_formatted_display_duration_never_uses_ceil_minutes_bug(): void
    {
        $announcement = new Announcement(['display_duration' => 30]);

        $this->assertNotSame('1m', $announcement->formattedDisplayDuration());
    }
}
