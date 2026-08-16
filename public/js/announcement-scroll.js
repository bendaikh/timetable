/**
 * Announcement scroll planner for the live display.
 *
 * Rules:
 * - Dashboard display_duration is how long the card stays on screen.
 * - Scroll speed controls how fast text moves (within that window).
 * - Long text still reaches the last line before the card rotates away.
 * - Short start pause so the first line can be read; short end pause on the last line.
 */
(function (root, factory) {
    var api = factory();
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    }
    if (typeof root === 'object' && root !== null) {
        root.AnnouncementScroll = api;
    }
})(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    var START_HOLD_SEC = 0.9;
    var END_HOLD_SEC = 1.1;
    var MIN_MOTION_SEC = 3;
    var MIN_END_PADDING_PX = 40;

    function toPositiveNumber(value, fallback) {
        var n = Number(value);
        return Number.isFinite(n) && n > 0 ? n : fallback;
    }

    function preferredPixelsPerSecond(scrollSpeed) {
        // Speed 1 ≈ slow/readable TV crawl, speed 10 ≈ brisk.
        return Math.max(16, 10 + scrollSpeed * 8);
    }

    /**
     * @param {object} input
     * @param {number} input.contentHeight
     * @param {number} input.containerHeight
     * @param {number} [input.lineHeight]
     * @param {number} [input.scrollSpeed]
     * @param {number} [input.configuredDisplaySeconds]
     */
    function computeAnnouncementScrollPlan(input) {
        input = input || {};
        var lineHeight = toPositiveNumber(input.lineHeight, 20);
        var contentHeight = Math.ceil(toPositiveNumber(input.contentHeight, 0));
        var containerHeight = Math.ceil(Math.max(0, Number(input.containerHeight) || 0));
        var scrollSpeed = Math.max(1, Math.min(10, toPositiveNumber(input.scrollSpeed, 3)));
        var configuredDisplaySeconds = Math.max(1, toPositiveNumber(input.configuredDisplaySeconds, 10));
        var configuredDisplayDurationMs = Math.round(configuredDisplaySeconds * 1000);

        var overflowDistance = Math.max(0, contentHeight - containerHeight);
        var overflowThreshold = Math.max(2, lineHeight * 0.3);
        var isOverflowing = containerHeight > 0 && overflowDistance > overflowThreshold;

        if (!isOverflowing) {
            return {
                isOverflowing: false,
                overflowDistance: 0,
                endPaddingPx: 0,
                scrollDistance: 0,
                startHoldSec: 0,
                endHoldSec: 0,
                scrollMotionSec: 0,
                animationDurationSec: 0,
                configuredDisplayDurationMs: configuredDisplayDurationMs,
                requiredDisplayDurationMs: configuredDisplayDurationMs,
                pixelsPerSecond: preferredPixelsPerSecond(scrollSpeed),
            };
        }

        var endPaddingPx = Math.max(
            MIN_END_PADDING_PX,
            Math.ceil(lineHeight * 1.35),
            Math.ceil(containerHeight * 0.18)
        );
        var scrollDistance = overflowDistance + endPaddingPx;
        var pps = preferredPixelsPerSecond(scrollSpeed);
        var preferredMotionSec = Math.max(MIN_MOTION_SEC, scrollDistance / pps);

        var startHoldSec = Math.min(START_HOLD_SEC, configuredDisplaySeconds * 0.18);
        var endHoldSec = Math.min(END_HOLD_SEC, configuredDisplaySeconds * 0.2);
        startHoldSec = Math.max(0.45, startHoldSec);
        endHoldSec = Math.max(0.55, endHoldSec);

        var availableMotionSec = configuredDisplaySeconds - startHoldSec - endHoldSec;
        var scrollMotionSec;

        if (availableMotionSec < MIN_MOTION_SEC) {
            // Very short dashboard duration: shrink holds, keep some motion.
            startHoldSec = Math.min(0.45, configuredDisplaySeconds * 0.15);
            endHoldSec = Math.min(0.55, configuredDisplaySeconds * 0.18);
            availableMotionSec = Math.max(0.8, configuredDisplaySeconds - startHoldSec - endHoldSec);
            scrollMotionSec = availableMotionSec;
            pps = scrollDistance / scrollMotionSec;
        } else if (preferredMotionSec <= availableMotionSec) {
            // Preferred speed fits: scroll at the chosen speed, then rest on the last lines.
            scrollMotionSec = preferredMotionSec;
            endHoldSec = Math.max(endHoldSec, configuredDisplaySeconds - startHoldSec - scrollMotionSec);
        } else {
            // Content is long for this duration: speed up so the last line still appears.
            scrollMotionSec = availableMotionSec;
            pps = scrollDistance / scrollMotionSec;
        }

        return {
            isOverflowing: true,
            overflowDistance: overflowDistance,
            endPaddingPx: endPaddingPx,
            scrollDistance: scrollDistance,
            startHoldSec: startHoldSec,
            endHoldSec: endHoldSec,
            scrollMotionSec: scrollMotionSec,
            animationDurationSec: scrollMotionSec,
            configuredDisplayDurationMs: configuredDisplayDurationMs,
            requiredDisplayDurationMs: configuredDisplayDurationMs,
            pixelsPerSecond: pps,
        };
    }

    return {
        START_HOLD_SEC: START_HOLD_SEC,
        END_HOLD_SEC: END_HOLD_SEC,
        preferredPixelsPerSecond: preferredPixelsPerSecond,
        computeAnnouncementScrollPlan: computeAnnouncementScrollPlan,
    };
});
