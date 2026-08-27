<?php

namespace App\Support;

/**
 * Announcement-box geometry derived from the live CSS layout tokens in
 * public/css/fullscreen-display.css (--board-announce-width: 45%, flex main row).
 *
 * Header / special-times chrome is measured from the same stylesheet conventions
 * used on the mosque TV display (compact header + special times strip).
 */
class AnnouncementBoxGeometry
{
    /** CSS --board-announce-width */
    public const ANNOUNCE_WIDTH_RATIO = 0.45;

    /**
     * Approximate chrome below/above the main prayer+announcements row on the
     * digital board (header + gap + special times). Tuned against fullscreen-display.css.
     */
    public static function estimateMainRowHeight(int $viewportHeight): int
    {
        $header = (int) round($viewportHeight * 0.055);
        $gap = (int) max(8, min(14, round($viewportHeight * 0.01)));
        $special = (int) round($viewportHeight * 0.075);

        return max(1, $viewportHeight - $header - $gap - $special);
    }

    /**
     * @return array{
     *   width:int,
     *   height:int,
     *   ratio:float,
     *   aspect_label:string,
     *   safe_margin_pct:int,
     *   object_fit:string,
     *   viewport:array{width:int,height:int}
     * }
     */
    public static function forViewport(int $viewportWidth, int $viewportHeight): array
    {
        $width = (int) round($viewportWidth * self::ANNOUNCE_WIDTH_RATIO);
        $height = self::estimateMainRowHeight($viewportHeight);
        $ratio = $width / max(1, $height);

        return [
            'width' => $width,
            'height' => $height,
            'ratio' => round($ratio, 4),
            'aspect_label' => self::nearestAspectLabel($ratio),
            // object-fit: cover crops overflow — keep important content inset.
            'safe_margin_pct' => 5,
            'object_fit' => 'cover',
            'viewport' => [
                'width' => $viewportWidth,
                'height' => $viewportHeight,
            ],
        ];
    }

    /**
     * Client-facing poster spec. Mosque TVs are typically 16:9 4K (3840×2160).
     * Overlay fills the whole announcements column (including the green title bar).
     *
     * @return array{
     *   design_width:int,
     *   design_height:int,
     *   aspect_label:string,
     *   object_fit:string,
     *   safe_margin_pct:int,
     *   full_hd:array{width:int,height:int},
     *   uhd_4k:array{width:int,height:int},
     *   notes:list<string>
     * }
     */
    public static function recommendation(): array
    {
        $fhd = self::forViewport(1920, 1080);
        $uhd = self::forViewport(3840, 2160);

        return [
            'design_width' => $uhd['width'],
            'design_height' => $uhd['height'],
            'aspect_label' => $uhd['aspect_label'],
            'object_fit' => $uhd['object_fit'],
            'safe_margin_pct' => $uhd['safe_margin_pct'],
            'full_hd' => [
                'width' => $fhd['width'],
                'height' => $fhd['height'],
            ],
            'uhd_4k' => [
                'width' => $uhd['width'],
                'height' => $uhd['height'],
            ],
            'notes' => [
                'Posters cover only the announcements column (45% of screen width), not the full TV.',
                'Announcement box width is not configurable in admin (CSS --board-announce-width: 45%).',
                'Height is the main prayer+announcements row (viewport minus header, gap, and special-times strip).',
                'Hiding header or special times makes the box taller; showing sliding text makes it shorter. Cover will crop extra.',
                'Admin padding/border does not shrink the poster: overlay is inset:0 on #announcements-section.',
            ],
        ];
    }

    private static function nearestAspectLabel(float $ratio): string
    {
        // 10:11 ≈ 0.909
        if (abs($ratio - (10 / 11)) < 0.03) {
            return '10:11';
        }

        return sprintf('%.3f:1', $ratio);
    }
}
