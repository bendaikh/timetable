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
}
