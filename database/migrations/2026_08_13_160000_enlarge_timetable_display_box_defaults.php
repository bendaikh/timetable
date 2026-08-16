<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enlarge prayer typography, tighten prayer columns, and enlarge announcements
     * for existing installs still on the previous defaults.
     * Custom admin values that differ from the old defaults are left untouched.
     */
    public function up(): void
    {
        $this->updateHeaderBox();
        $this->updatePrayerTimesBox();
        $this->updateAnnouncementsBox();
    }

    public function down(): void
    {
        // Intentionally no-op: rolling back display typography would shrink the TV layout again.
    }

    private function updateHeaderBox(): void
    {
        $box = DB::table('box_settings')->where('box_type', 'header_box')->first();
        if (!$box) {
            return;
        }

        $styling = $this->decodeJson($box->styling_settings);
        $changed = false;

        if ($this->isLegacyRem($styling['date_font_size'] ?? null, ['1.6', '1.6rem', '1.2', '1.2rem'])) {
            $styling['date_font_size'] = '2.75rem';
            $changed = true;
        }

        if ($changed) {
            DB::table('box_settings')->where('id', $box->id)->update([
                'styling_settings' => json_encode($styling),
                'updated_at' => now(),
            ]);
        }
    }

    private function updatePrayerTimesBox(): void
    {
        $box = DB::table('box_settings')->where('box_type', 'prayer_times_box')->first();
        if (!$box) {
            return;
        }

        $styling = $this->decodeJson($box->styling_settings);
        $layout = $this->decodeJson($box->layout_settings);
        $stylingChanged = false;
        $layoutChanged = false;

        $fontUpgrades = [
            'prayer_names_font_size' => [['3', '3rem'], '4rem'],
            'beginning_font_size' => [['2', '2rem'], '3.5rem'],
            'jamaat_font_size' => [['2', '2rem'], '3.5rem'],
            'header_font_size' => [['1', '1rem', '1.2', '1.2rem'], '1.5rem'],
            'font_size' => [['2', '2rem'], '3.5rem'],
        ];

        foreach ($fontUpgrades as $key => [$legacyValues, $newValue]) {
            if ($this->isLegacyRem($styling[$key] ?? null, $legacyValues)) {
                $styling[$key] = $newValue;
                $stylingChanged = true;
            }
        }

        if (in_array((string) ($styling['padding'] ?? ''), ['20', '20px'], true)) {
            $styling['padding'] = '16';
            $stylingChanged = true;
        }

        $widths = $layout['column_widths'] ?? null;
        if (is_array($widths) && count($widths) >= 3) {
            $normalized = array_map(static fn ($w) => trim((string) $w), array_slice($widths, 0, 3));
            if ($normalized === ['45%', '25%', '25%'] || $normalized === ['45', '25', '25']) {
                $layout['column_widths'] = ['30%', '35%', '35%'];
                $layoutChanged = true;
            }
        } elseif (!is_array($widths) || count($widths) < 3) {
            $layout['column_widths'] = ['30%', '35%', '35%'];
            $layoutChanged = true;
        }

        $update = ['updated_at' => now()];
        if ($stylingChanged) {
            $update['styling_settings'] = json_encode($styling);
        }
        if ($layoutChanged) {
            $update['layout_settings'] = json_encode($layout);
        }

        if ($stylingChanged || $layoutChanged) {
            DB::table('box_settings')->where('id', $box->id)->update($update);
        }
    }

    private function updateAnnouncementsBox(): void
    {
        $box = DB::table('box_settings')->where('box_type', 'announcements_box')->first();
        if (!$box) {
            return;
        }

        $styling = $this->decodeJson($box->styling_settings);
        $changed = false;

        if ($this->isLegacyRem($styling['title_font_size'] ?? null, ['1.2', '1.2rem'])) {
            $styling['title_font_size'] = '1.6rem';
            $changed = true;
        }

        if ($this->isLegacyRem($styling['font_size'] ?? null, ['1.5', '1.5rem'])) {
            $styling['font_size'] = '2rem';
            $changed = true;
        }

        if (in_array((string) ($styling['padding'] ?? ''), ['20', '20px'], true)) {
            $styling['padding'] = '16';
            $changed = true;
        }

        if ($changed) {
            DB::table('box_settings')->where('id', $box->id)->update([
                'styling_settings' => json_encode($styling),
                'updated_at' => now(),
            ]);
        }
    }

    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    private function isLegacyRem(mixed $value, array $legacyValues): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, array_map('strtolower', $legacyValues), true);
    }
};
