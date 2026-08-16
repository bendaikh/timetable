<?php

namespace Tests\Unit;

use App\Support\CssUnits;
use PHPUnit\Framework\TestCase;

class CssUnitsTest extends TestCase
{
    public function test_normalize_rem_returns_default_for_empty_values(): void
    {
        $this->assertSame('1.2rem', CssUnits::normalizeRem(null));
        $this->assertSame('1.2rem', CssUnits::normalizeRem(''));
    }

    public function test_normalize_rem_preserves_rem_values(): void
    {
        $this->assertSame('1.75rem', CssUnits::normalizeRem('1.75rem'));
    }

    public function test_normalize_rem_converts_px_values(): void
    {
        $this->assertSame('1.75rem', CssUnits::normalizeRem('28px'));
        $this->assertSame('3.125rem', CssUnits::normalizeRem('50px'));
    }

    public function test_normalize_rem_converts_legacy_numeric_px_values(): void
    {
        $this->assertSame('1.75rem', CssUnits::normalizeRem('28'));
        $this->assertSame('2.25rem', CssUnits::normalizeRem('36'));
    }

    public function test_normalize_box_rem_treats_bare_numbers_as_rem(): void
    {
        $this->assertSame('3rem', CssUnits::normalizeBoxRem('3'));
        $this->assertSame('1.4rem', CssUnits::normalizeBoxRem('1.4'));
        $this->assertSame('5rem', CssUnits::normalizeBoxRem('5'));
    }

    public function test_normalize_box_rem_preserves_rem_suffix(): void
    {
        $this->assertSame('3rem', CssUnits::normalizeBoxRem('3rem'));
        $this->assertSame('1.6rem', CssUnits::normalizeBoxRem('1.6rem'));
    }

    public function test_normalize_box_rem_converts_px_suffix(): void
    {
        $this->assertSame('1rem', CssUnits::normalizeBoxRem('16px'));
    }

    public function test_normalize_prayer_column_widths_fills_underfilled_rows(): void
    {
        $this->assertSame(
            ['30%', '35%', '35%'],
            CssUnits::normalizePrayerColumnWidths(['30%', '25%', '30%'])
        );
    }

    public function test_normalize_prayer_column_widths_keeps_balanced_rows(): void
    {
        $this->assertSame(
            ['30%', '35%', '35%'],
            CssUnits::normalizePrayerColumnWidths(['30%', '35%', '35%'])
        );
    }

    public function test_normalize_prayer_column_widths_uses_defaults_when_missing(): void
    {
        $this->assertSame(
            ['30%', '35%', '35%'],
            CssUnits::normalizePrayerColumnWidths(null)
        );
    }

    public function test_normalize_prayer_column_widths_scales_near_full_rows(): void
    {
        $this->assertSame(
            ['47.368%', '26.316%', '26.316%'],
            CssUnits::normalizePrayerColumnWidths(['45%', '25%', '25%'])
        );
    }

    public function test_normalize_prayer_column_widths_keeps_exact_full_rows(): void
    {
        $this->assertSame(
            ['45%', '25%', '30%'],
            CssUnits::normalizePrayerColumnWidths(['45%', '25%', '30%'])
        );
    }

    public function test_normalize_announcement_rem_converts_legacy_px_integers(): void
    {
        $this->assertSame('1.5rem', CssUnits::normalizeAnnouncementRem(24));
        $this->assertSame('2.25rem', CssUnits::normalizeAnnouncementRem(36));
        $this->assertSame('1.75rem', CssUnits::normalizeAnnouncementRem('28px'));
    }

    public function test_normalize_announcement_rem_treats_small_numbers_as_rem(): void
    {
        $this->assertSame('2.25rem', CssUnits::normalizeAnnouncementRem(2.25));
        $this->assertSame('1.5rem', CssUnits::normalizeAnnouncementRem('1.5'));
        $this->assertSame('3rem', CssUnits::normalizeAnnouncementRem('3rem'));
    }
}
