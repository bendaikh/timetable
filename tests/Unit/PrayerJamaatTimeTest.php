<?php

namespace Tests\Unit;

use App\Models\PrayerTime;
use App\Models\Setting;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrayerJamaatTimeTest extends TestCase
{
    private const TZ = 'Europe/London';

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
            $table->time('zohar')->nullable();
            $table->time('zohar_jamaat')->nullable();
            $table->timestamps();
        });

        Setting::set('timezone', self::TZ);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_resolve_anchors_clock_time_to_prayer_row_date_and_timezone(): void
    {
        $record = PrayerTime::query()->create([
            'date' => '2026-07-05',
            'zohar' => '12:30:00',
            'zohar_jamaat' => '13:15:00',
        ]);

        $resolved = PrayerJamaatTime::resolve(
            $record,
            'zohar',
            Carbon::parse('2026-07-05 08:00:00', self::TZ)
        );

        $this->assertNotNull($resolved);
        $this->assertSame(self::TZ, $resolved->timezoneName);
        $this->assertSame('2026-07-05 13:15:00', $resolved->format('Y-m-d H:i:s'));
    }

    public function test_normalize_clock_value_accepts_hh_mm_strings(): void
    {
        $this->assertSame('13:15:00', PrayerJamaatTime::normalizeClockValue('13:15'));
    }
}
