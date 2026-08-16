<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make announcement titles optional and store title/body font sizes as rem
     * (legacy integer columns stored px values).
     */
    public function up(): void
    {
        // Convert legacy px integers → rem before changing column types.
        $rows = DB::table('announcements')->select('id', 'font_size', 'title_font_size')->get();
        foreach ($rows as $row) {
            DB::table('announcements')->where('id', $row->id)->update([
                'font_size' => $this->legacyPxToRem($row->font_size, 1.5),
                'title_font_size' => $this->legacyPxToRem($row->title_font_size, 2.25),
            ]);
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->decimal('font_size', 8, 3)->default(1.5)->change();
            $table->decimal('title_font_size', 8, 3)->default(2.25)->change();
        });
    }

    public function down(): void
    {
        $rows = DB::table('announcements')->select('id', 'font_size', 'title_font_size')->get();
        foreach ($rows as $row) {
            DB::table('announcements')->where('id', $row->id)->update([
                'font_size' => $this->remToLegacyPx($row->font_size, 24),
                'title_font_size' => $this->remToLegacyPx($row->title_font_size, 36),
            ]);
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->integer('font_size')->default(24)->change();
            $table->integer('title_font_size')->default(36)->change();
        });
    }

    private function legacyPxToRem(mixed $value, float $defaultRem): float
    {
        if ($value === null || $value === '') {
            return $defaultRem;
        }

        $numeric = (float) $value;
        if ($numeric <= 0) {
            return $defaultRem;
        }

        // Historical announcement sizes were px integers (typically 12–160).
        // Rem values after conversion are small (≈0.75–10).
        if ($numeric > 10) {
            return round($numeric / 16, 3);
        }

        return round($numeric, 3);
    }

    private function remToLegacyPx(mixed $value, int $defaultPx): int
    {
        if ($value === null || $value === '') {
            return $defaultPx;
        }

        $numeric = (float) $value;
        if ($numeric <= 0) {
            return $defaultPx;
        }

        if ($numeric <= 10) {
            return (int) round($numeric * 16);
        }

        return (int) round($numeric);
    }
};
