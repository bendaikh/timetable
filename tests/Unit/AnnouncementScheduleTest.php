<?php

namespace Tests\Unit;

use App\Models\Announcement;
use App\Models\Setting;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AnnouncementScheduleTest extends TestCase
{
    private const TZ = 'Europe/London';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('settings');
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Setting::set('timezone', self::TZ);
        config(['app.timezone' => self::TZ]);
    }

    public function test_combine_schedule_date_time_defaults_start_to_midnight(): void
    {
        $start = Announcement::combineScheduleDateTime('2026-08-13', null, false);

        $this->assertNotNull($start);
        $this->assertSame('2026-08-13 00:00:00', $start->format('Y-m-d H:i:s'));
    }

    public function test_combine_schedule_date_time_defaults_end_to_end_of_day_minute(): void
    {
        $end = Announcement::combineScheduleDateTime('2026-08-13', null, true);

        $this->assertNotNull($end);
        $this->assertSame('2026-08-13 23:59:59', $end->format('Y-m-d H:i:s'));
    }

    public function test_combine_schedule_date_time_uses_hours_and_minutes(): void
    {
        $start = Announcement::combineScheduleDateTime('2026-08-13', '14:30', false);
        $end = Announcement::combineScheduleDateTime('2026-08-13', '16:00', true);

        $this->assertSame('2026-08-13 14:30:00', $start->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-13 16:00:59', $end->format('Y-m-d H:i:s'));
        $this->assertSame(self::TZ, $start->timezoneName);
    }

    public function test_is_within_schedule_respects_start_and_end_times(): void
    {
        $announcement = new Announcement();
        $announcement->forceFill([
            'start_date' => '2026-08-13 14:00:00',
            'end_date' => '2026-08-13 16:00:59',
        ]);

        $this->assertFalse($announcement->isWithinSchedule(Carbon::parse('2026-08-13 13:59:59', self::TZ)));
        $this->assertTrue($announcement->isWithinSchedule(Carbon::parse('2026-08-13 14:00:00', self::TZ)));
        $this->assertTrue($announcement->isWithinSchedule(Carbon::parse('2026-08-13 15:30:00', self::TZ)));
        $this->assertTrue($announcement->isWithinSchedule(Carbon::parse('2026-08-13 16:00:30', self::TZ)));
        $this->assertFalse($announcement->isWithinSchedule(Carbon::parse('2026-08-13 16:01:00', self::TZ)));
    }

    public function test_open_ended_schedule_is_always_within_window(): void
    {
        $announcement = new Announcement();
        $announcement->forceFill([
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->assertTrue($announcement->isWithinSchedule(PrayerJamaatTime::now()));
    }

    public function test_friday_only_matches_only_on_friday_in_mosque_timezone(): void
    {
        $announcement = new Announcement();
        $announcement->forceFill([
            'is_active' => true,
            'auto_repeat' => true,
            'repeat_days' => ['friday'],
            'start_date' => null,
            'end_date' => null,
        ]);

        // 2026-08-14 is Friday in Europe/London
        $friday = Carbon::parse('2026-08-14 12:00:00', self::TZ);
        $saturday = Carbon::parse('2026-08-15 12:00:00', self::TZ);

        $this->assertTrue($announcement->matchesRepeatDays($friday));
        $this->assertTrue($announcement->isActiveToday($friday));
        $this->assertFalse($announcement->matchesRepeatDays($saturday));
        $this->assertFalse($announcement->isActiveToday($saturday));
    }

    public function test_without_day_restriction_matches_every_day(): void
    {
        $announcement = new Announcement();
        $announcement->forceFill([
            'is_active' => true,
            'auto_repeat' => false,
            'repeat_days' => null,
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->assertTrue($announcement->matchesRepeatDays(Carbon::parse('2026-08-14 12:00:00', self::TZ)));
        $this->assertTrue($announcement->matchesRepeatDays(Carbon::parse('2026-08-15 12:00:00', self::TZ)));
    }

    public function test_legacy_days_without_auto_repeat_flag_still_restrict(): void
    {
        // Older saves left days checked while auto_repeat was off.
        $announcement = new Announcement();
        $announcement->forceFill([
            'is_active' => true,
            'auto_repeat' => false,
            'repeat_days' => ['wednesday', 'thursday'],
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->assertTrue($announcement->matchesRepeatDays(Carbon::parse('2026-08-13 10:00:00', self::TZ))); // Thursday
        $this->assertFalse($announcement->matchesRepeatDays(Carbon::parse('2026-08-16 10:00:00', self::TZ))); // Sunday
    }

    public function test_next_display_order_increments_from_max(): void
    {
        Schema::dropIfExists('announcements');
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_repeat')->default(false);
            $table->json('repeat_days')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->timestamps();
        });

        $this->assertSame(1, Announcement::nextDisplayOrder());

        Announcement::create([
            'title' => 'First',
            'content' => 'One',
            'is_active' => true,
            'display_order' => 3,
        ]);
        Announcement::create([
            'title' => 'Second',
            'content' => 'Two',
            'is_active' => true,
            'display_order' => 5,
        ]);

        $this->assertSame(6, Announcement::nextDisplayOrder());
    }

    public function test_get_active_announcements_orders_by_display_order_ascending(): void
    {
        Schema::dropIfExists('announcements');
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_repeat')->default(false);
            $table->json('repeat_days')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->unsignedInteger('display_order')->default(1);
            $table->integer('display_duration')->default(10);
            $table->integer('scroll_speed')->default(3);
            $table->timestamps();
        });

        Announcement::create([
            'title' => 'Third',
            'content' => 'C',
            'is_active' => true,
            'display_order' => 3,
        ]);
        Announcement::create([
            'title' => 'First',
            'content' => 'A',
            'is_active' => true,
            'display_order' => 1,
        ]);
        Announcement::create([
            'title' => 'Second',
            'content' => 'B',
            'is_active' => true,
            'display_order' => 2,
        ]);

        $ordered = Announcement::getActiveAnnouncements()->pluck('title')->all();

        $this->assertSame(['First', 'Second', 'Third'], $ordered);
    }
}
