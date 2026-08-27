<?php

namespace Tests\Unit;

use App\Models\Media;
use App\Models\MediaSchedule;
use App\Models\PrayerTime;
use App\Models\Setting;
use App\Services\MediaDisplayService;
use App\Support\AnnouncementBoxGeometry;
use App\Support\MediaScheduleDuration;
use App\Support\MosqueTimezone;
use App\Support\PrayerJamaatTime;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Final acceptance evidence for the mosque poster system (admin call requirements).
 */
class PosterSystemFinalAuditTest extends TestCase
{
    private const TZ = 'Europe/London';

    private MediaDisplayService $service;

    private string $date = '2026-07-05';

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('media_schedule_media');
        Schema::dropIfExists('media_schedules');
        Schema::dropIfExists('media');
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
            $table->time('zohar_adhan')->nullable();
            $table->time('zohar_jamaat')->nullable();
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path');
            $table->string('type')->default('image');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_duration')->default(30);
            $table->timestamps();
        });

        Schema::create('media_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_type');
            $table->string('prayer_name')->nullable();
            $table->integer('minutes_before_prayer')->nullable();
            $table->integer('minutes_after_prayer')->nullable();
            $table->json('days_of_week')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('media_schedule_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_schedule_id');
            $table->foreignId('media_id');
            $table->decimal('duration', 12, 6)->default(1);
            $table->integer('priority')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->time('start_time')->nullable();
            $table->date('expiry_date')->nullable();
            $table->time('expiry_time')->nullable();
            $table->integer('gap_duration')->default(0);
            $table->json('days_of_week')->nullable();
            $table->timestamps();
        });

        config(['app.timezone' => 'UTC']);
        date_default_timezone_set('UTC');
        Setting::set('timezone', self::TZ);
        MosqueTimezone::apply(self::TZ);

        $this->service = app(MediaDisplayService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_requirement1_overlay_is_nested_in_announcements_section_not_fullscreen_fixed(): void
    {
        $blade = file_get_contents(resource_path('views/timetable/index.blade.php'));
        $css = file_get_contents(public_path('css/fullscreen-display.css'));
        $layout = file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertStringContainsString(
            '{!! $mediaOverlayMarkup !!}',
            $blade,
            'Overlay markup must be injected into the announcements section'
        );
        // Overlay is a sibling of #announcements-content, child of #announcements-section
        $this->assertMatchesRegularExpression(
            '/id="announcements-content"[\s\S]+?<\/div>\s*\{\{-- Posters\/media only cover this announcements box[\s\S]*?\{!! \$mediaOverlayMarkup !!\}/',
            $blade
        );
        $this->assertMatchesRegularExpression(
            '/\{!! \$mediaOverlayMarkup !!\}\s*<\/div>\s*<\/div>/',
            $blade
        );

        $this->assertMatchesRegularExpression(
            '/#announcements-section\s*>\s*\.media-overlay[\s\S]{0,200}position:\s*absolute/',
            $css
        );
        $this->assertDoesNotMatchRegularExpression(
            '/#announcements-section\s*>\s*\.media-overlay[\s\S]{0,200}position:\s*fixed/',
            $css
        );
        $this->assertMatchesRegularExpression(
            '/\.announcements-section\s*\{[\s\S]{0,300}overflow:\s*hidden/',
            $css
        );
        $this->assertStringContainsString('object-fit: cover', $css);
        $this->assertStringContainsString('object-fit: cover', $blade);
        $this->assertStringNotContainsString(
            "object-fit: contain; position: relative; z-index: 1;",
            $blade
        );

        // Global layout must not reintroduce full-viewport fixed posters.
        $this->assertMatchesRegularExpression(
            '/\.media-overlay\s*\{[\s\S]{0,120}position:\s*absolute/',
            $layout
        );
        $this->assertDoesNotMatchRegularExpression(
            '/\.media-overlay\s*\{[\s\S]{0,80}position:\s*fixed[\s\S]{0,80}100vw/',
            $layout
        );
    }

    public function test_requirement3_ten_second_duration_is_accepted_and_played(): void
    {
        $seconds = 10;
        $validator = Validator::make(
            ['media_durations' => [$seconds]],
            ['media_durations.*' => 'required|numeric|min:' . MediaScheduleDuration::MIN_SECONDS]
        );
        $this->assertTrue($validator->passes(), '10s must pass validation');

        $fiveSec = Validator::make(
            ['media_durations' => [5]],
            ['media_durations.*' => 'required|numeric|min:' . MediaScheduleDuration::MIN_SECONDS]
        );
        $this->assertTrue($fiveSec->passes(), '5s must pass validation');
        $this->assertSame(5, MediaScheduleDuration::secondsForStorage(5));
        $this->assertSame(5, MediaScheduleDuration::secondsFromStored(5));

        $stored = MediaScheduleDuration::secondsForStorage($seconds);
        $this->assertSame($seconds, $stored);
        $this->assertSame(
            $seconds,
            MediaScheduleDuration::secondsFromStored($stored)
        );

        // Below documented minimum must fail (legacy 30s floor is gone; 4s still too short).
        $tooShort = Validator::make(
            ['media_durations' => [4]],
            ['media_durations.*' => 'required|numeric|min:' . MediaScheduleDuration::MIN_SECONDS]
        );
        $this->assertTrue($tooShort->fails());

        // Client accepts 5–10s minimum; old 30s floor must not apply.
        $this->assertSame(5, MediaScheduleDuration::MIN_SECONDS);
        $this->assertLessThan(30, MediaScheduleDuration::MIN_SECONDS);

        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => '12:00:00',
            'zohar_jamaat' => '13:00:00',
        ]);

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'full_time_poster',
            'is_active' => true,
        ]);

        $media10 = Media::query()->create([
            'title' => 'TenSec',
            'file_path' => 'media/10.png',
            'type' => 'image',
            'is_active' => true,
        ]);
        $media20 = Media::query()->create([
            'title' => 'TwentySec',
            'file_path' => 'media/20.png',
            'type' => 'image',
            'is_active' => true,
        ]);

        $schedule->mediaItems()->attach($media10->id, [
            'duration' => MediaScheduleDuration::secondsForStorage(10),
            'priority' => 1,
            'is_active' => true,
            'gap_duration' => 0,
        ]);
        $schedule->mediaItems()->attach($media20->id, [
            'duration' => MediaScheduleDuration::secondsForStorage(20),
            'priority' => 2,
            'is_active' => true,
            'gap_duration' => 0,
        ]);

        Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:00', self::TZ));
        $first = $this->service->getCurrentMedia();
        $this->assertNotNull($first);
        $this->assertSame('TenSec', $first['media']->title);
        $this->assertSame(10, $first['duration']);

        Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:10', self::TZ));
        $second = $this->service->getCurrentMedia();
        $this->assertNotNull($second);
        $this->assertSame('TwentySec', $second['media']->title);
        $this->assertSame(20, $second['duration']);
    }

    public function test_requirement4_four_and_five_active_posters_all_cycle(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:00', self::TZ));

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'full_time_poster',
            'is_active' => true,
        ]);

        $titles = [];
        for ($i = 1; $i <= 5; $i++) {
            $media = Media::query()->create([
                'title' => "Poster{$i}",
                'file_path' => "media/{$i}.png",
                'type' => 'image',
                'is_active' => true,
            ]);
            $schedule->mediaItems()->attach($media->id, [
                'duration' => MediaScheduleDuration::secondsForStorage(10),
                'priority' => $i,
                'is_active' => true,
                'gap_duration' => 0,
            ]);
            $titles[] = "Poster{$i}";
        }

        $seen = [];
        for ($offset = 0; $offset < 50; $offset += 10) {
            Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:00', self::TZ)->addSeconds($offset));
            $current = $this->service->getCurrentMedia();
            $this->assertNotNull($current, "Expected poster at +{$offset}s");
            $seen[] = $current['media']->title;
            $this->assertSame(10, $current['duration']);
        }

        $this->assertSame($titles, $seen, 'Priorities 1–5 must all appear in order; no hardcoded limit of 2');
        $this->assertCount(5, array_unique($seen));
    }

    public function test_requirement4_four_posters_across_two_full_time_schedules_all_cycle(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:00', self::TZ));

        $scheduleA = MediaSchedule::query()->create([
            'schedule_type' => 'full_time_poster',
            'is_active' => true,
        ]);
        $scheduleB = MediaSchedule::query()->create([
            'schedule_type' => 'full_time_poster',
            'is_active' => true,
        ]);

        foreach ([1 => $scheduleA, 2 => $scheduleA, 3 => $scheduleB, 4 => $scheduleB] as $priority => $schedule) {
            $media = Media::query()->create([
                'title' => "Split{$priority}",
                'file_path' => "media/split{$priority}.png",
                'type' => 'image',
                'is_active' => true,
            ]);
            $schedule->mediaItems()->attach($media->id, [
                'duration' => MediaScheduleDuration::secondsForStorage(10),
                'priority' => $priority,
                'is_active' => true,
                'gap_duration' => 0,
            ]);
        }

        $seen = [];
        for ($offset = 0; $offset < 40; $offset += 10) {
            Carbon::setTestNow(Carbon::parse($this->date . ' 00:00:00', self::TZ)->addSeconds($offset));
            $current = $this->service->getCurrentMedia();
            $this->assertNotNull($current, "Expected poster at +{$offset}s");
            $seen[] = $current['media']->title;
        }

        $this->assertSame(['Split1', 'Split2', 'Split3', 'Split4'], $seen);
    }

    public function test_requirement4_prayer_schedules_merge_and_override_full_time(): void
    {
        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => '12:00:00',
            'zohar_jamaat' => '13:00:00',
        ]);

        $fullTime = MediaSchedule::query()->create([
            'schedule_type' => 'full_time_poster',
            'is_active' => true,
        ]);
        $fullMedia = Media::query()->create([
            'title' => 'FullTimeOnly',
            'file_path' => 'media/ft.png',
            'type' => 'image',
            'is_active' => true,
        ]);
        $fullTime->mediaItems()->attach($fullMedia->id, [
            'duration' => MediaScheduleDuration::secondsForStorage(10),
            'priority' => 1,
            'is_active' => true,
            'gap_duration' => 0,
        ]);

        $beforeA = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => 30,
            'is_active' => true,
        ]);
        $beforeB = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => 20,
            'is_active' => true,
        ]);

        foreach ([['A', $beforeA, 1], ['B', $beforeA, 2], ['C', $beforeB, 3], ['D', $beforeB, 4]] as [$label, $schedule, $priority]) {
            $media = Media::query()->create([
                'title' => "Prayer{$label}",
                'file_path' => "media/p{$label}.png",
                'type' => 'image',
                'is_active' => true,
            ]);
            $schedule->mediaItems()->attach($media->id, [
                'duration' => MediaScheduleDuration::secondsForStorage(10),
                'priority' => $priority,
                'is_active' => true,
                'gap_duration' => 0,
            ]);
        }

        // Outside prayer windows: full-time only.
        Carbon::setTestNow(Carbon::parse($this->date . ' 10:00:00', self::TZ));
        $outside = $this->service->getCurrentMedia();
        $this->assertNotNull($outside);
        $this->assertSame('FullTimeOnly', $outside['media']->title);

        // Overlapping before-prayer windows (avoid 12:40 adhan countdown): all four prayer posters.
        // Earliest prayer window starts 12:30; at 12:50 elapsed % 40s cycle == 0 → PrayerA.
        $sampleStart = Carbon::parse($this->date . ' 12:50:00', self::TZ);
        $seen = [];
        for ($offset = 0; $offset < 40; $offset += 10) {
            Carbon::setTestNow($sampleStart->copy()->addSeconds($offset));
            $current = $this->service->getCurrentMedia();
            $this->assertNotNull($current, "Expected prayer poster at +{$offset}s");
            $this->assertNotSame('FullTimeOnly', $current['media']->title);
            $seen[] = $current['media']->title;
        }

        $this->assertSame(['PrayerA', 'PrayerB', 'PrayerC', 'PrayerD'], $seen);
    }

    public function test_requirement5_announcement_box_dimensions_match_layout_tokens(): void
    {
        $css = file_get_contents(public_path('css/fullscreen-display.css'));
        $this->assertStringContainsString('--board-announce-width: 45%', $css);
        $this->assertStringContainsString('object-fit: cover', $css);

        $hd = AnnouncementBoxGeometry::forViewport(1920, 1080);
        $uhd = AnnouncementBoxGeometry::forViewport(3840, 2160);

        $this->assertSame(864, $hd['width']);
        $this->assertSame(929, $hd['height']);
        $this->assertSame(1728, $uhd['width']);
        $this->assertSame(1865, $uhd['height']);
        $this->assertSame(45, (int) round(AnnouncementBoxGeometry::ANNOUNCE_WIDTH_RATIO * 100));
        $this->assertSame('10:11', $uhd['aspect_label']);
        $this->assertSame(5, $uhd['safe_margin_pct']);
        $this->assertSame('cover', $uhd['object_fit']);

        $spec = AnnouncementBoxGeometry::recommendation();
        $this->assertSame(1728, $spec['design_width']);
        $this->assertSame(1865, $spec['design_height']);
        $this->assertSame(['width' => 864, 'height' => 929], $spec['full_hd']);

        // Height must be main-row height (viewport minus header/gap/special chrome).
        $this->assertSame(
            AnnouncementBoxGeometry::estimateMainRowHeight(2160),
            $uhd['height']
        );
        $this->assertSame(
            AnnouncementBoxGeometry::estimateMainRowHeight(1080),
            $hd['height']
        );
    }

    public function test_requirement2_jamaat_reference_still_holds_under_final_audit(): void
    {
        Carbon::setTestNow(Carbon::parse($this->date . ' 12:00:00', self::TZ));
        PrayerTime::query()->create([
            'date' => $this->date,
            'zohar' => '12:50:00',
            'zohar_adhan' => '13:00:00',
            'zohar_jamaat' => '13:15:00',
        ]);

        $schedule = MediaSchedule::query()->create([
            'schedule_type' => 'minutes_before_prayer',
            'prayer_name' => 'zohar',
            'minutes_before_prayer' => 5,
            'is_active' => true,
        ]);

        $row = PrayerTime::query()->first();
        $window = PrayerJamaatTime::beforePrayerPosterWindow($schedule, $row);
        $this->assertSame('jamaat', $window['reference']);
        $this->assertSame('13:10:00', $window['start']->format('H:i:s'));
        $this->assertSame('13:15:00', $window['end']->format('H:i:s'));
        $this->assertSame(self::TZ, PrayerJamaatTime::appTimezone());
    }
}
