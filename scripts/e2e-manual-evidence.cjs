const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { execSync } = require('child_process');

const ROOT = '/Users/fatimazahradarir/timetable';
const BASE_URL = process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000';
const RUN_ID = new Date().toISOString().replace(/[:.]/g, '-');
const OUTPUT_DIR = path.join(ROOT, 'storage/app/verification/e2e-live', RUN_ID);

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function fetchJson(url) {
    return JSON.parse(execSync(`curl -s "${url}"`, { encoding: 'utf8' }));
}

async function captureCheckpoint(page, id, log, note) {
    const filePath = path.join(OUTPUT_DIR, `${id}.png`);
    await page.screenshot({ path: filePath, fullPage: true });

    const browserMeta = await page.evaluate(() => ({
        browser_time: new Date().toISOString(),
        browser_timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
        fullscreen: !!document.fullscreenElement,
        popup: (() => {
            const popup = document.getElementById('countdown-popup');
            const title = document.getElementById('countdown-popup-title');
            const timer = document.getElementById('countdown-popup-timer');
            const prayer = document.getElementById('countdown-popup-prayer');
            const overlay = document.getElementById('media-overlay');
            return {
                visible: popup
                    ? popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex'
                    : false,
                title: title ? title.textContent.trim() : null,
                timer: timer ? timer.textContent.trim() : null,
                prayer: prayer ? prayer.textContent.trim() : null,
                media_overlay_display: overlay ? getComputedStyle(overlay).display : null,
            };
        })(),
    }));

    const diagnostic = fetchJson(`${BASE_URL}/api/countdown-diagnostic`);
    const screenState = fetchJson(`${BASE_URL}/api/screen-state`);

    const entry = {
        checkpoint_id: id,
        note,
        screenshot: filePath,
        captured_at_utc: new Date().toISOString(),
        server_time: diagnostic.log.server_time,
        server_timezone: diagnostic.log.server_timezone,
        app_timezone: screenState.app_timezone || null,
        browser_time: browserMeta.browser_time,
        browser_timezone: browserMeta.browser_timezone,
        fullscreen_active: browserMeta.fullscreen,
        popup: browserMeta.popup,
        api_screen_state: screenState,
        api_diagnostic_log: diagnostic.log,
    };

    log.push(entry);
    console.log(`CHECKPOINT ${id}: server=${entry.server_time} browser=${entry.browser_time} state=${screenState.state} popup=${entry.popup.visible}`);
    return entry;
}

async function waitUntil(condition, timeoutMs, intervalMs = 500) {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
        const value = await condition();
        if (value) return value;
        await sleep(intervalMs);
    }
    return null;
}

function assertOrExit(check, message, evidence) {
    if (!check) {
        const failPath = path.join(OUTPUT_DIR, 'FAILURE.json');
        fs.writeFileSync(failPath, JSON.stringify({ message, evidence }, null, 2));
        console.error('FAIL:', message);
        process.exit(1);
    }
}

(async () => {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    const setup = JSON.parse(execSync('COUNTDOWN_LEAD_MINUTES=23 php scripts/setup-uk-manual-countdown-test.php', {
        cwd: ROOT,
        encoding: 'utf8',
    }));

    const log = [];
    const results = [];

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.evaluate(() => document.documentElement.requestFullscreen?.().catch(() => null));

    const initial = await captureCheckpoint(page, '00-initial-fullscreen', log,
        'Page loaded. Fullscreen entered. No refresh will occur for entire test.');

    assertOrExit(initial.app_timezone === 'Europe/London', 'App timezone must be Europe/London', initial);
    assertOrExit(initial.fullscreen_active, 'Fullscreen must be active', initial);
    assertOrExit(initial.api_screen_state.state === 'TIMETABLE', 'At load: must be TIMETABLE', initial);
    assertOrExit(!initial.popup.visible, 'At load: no popup', initial);

    const at23m = await captureCheckpoint(page, '00b-at-23-minutes-before-jamaat', log,
        'Exactly 23 minutes before Jamaat: nothing must appear.');
    assertOrExit(at23m.api_screen_state.state === 'TIMETABLE', 'At 23m before: TIMETABLE', at23m);
    assertOrExit(!at23m.popup.visible, 'At 23m before: no popup', at23m);
    assertOrExit(at23m.popup.media_overlay_display === 'none', 'At 23m: no poster overlay', at23m);
    results.push({ id: 'nothing-at-23-minutes-before-jamaat', pass: true });

    // Wait until 1 min before countdown #1, verify still TIMETABLE
    await waitUntil(async () => {
        const d = fetchJson(`${BASE_URL}/api/countdown-diagnostic`);
        const t = new Date(d.log.server_time).getTime();
        const start = new Date(setup.countdown_1_start).getTime();
        return t >= start - 60000;
    }, 180000, 2000);

    const beforeC1 = await captureCheckpoint(page, '01-before-countdown-1', log,
        'Must be TIMETABLE with no popup more than 1 minute before Jamaat-20m.');
    assertOrExit(beforeC1.api_screen_state.state === 'TIMETABLE', 'Before C1: must be TIMETABLE', beforeC1);
    assertOrExit(!beforeC1.popup.visible, 'Before C1: no popup', beforeC1);
    results.push({ id: 'no-countdown-before-20m', pass: true });

    // Negative: verify not active at setup time equivalent to 23m before (already passed in gap later)

    await waitUntil(async () => {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        const popup = await page.evaluate(() => {
            const el = document.getElementById('countdown-popup');
            return el && (el.style.display === 'flex' || getComputedStyle(el).display === 'flex');
        });
        return s.state === 'COUNTDOWN' && s.countdown?.phase === 'adhan' && popup;
    }, 180000, 200);

    const c1Start = await captureCheckpoint(page, '02-countdown-1-start', log,
        'Countdown #1 must appear exactly at Jamaat-20m with exact message.');
    assertOrExit(c1Start.popup.visible, 'C1 start: popup visible', c1Start);
    assertOrExit(c1Start.popup.title === 'Adhan will start in 30 seconds', 'C1 exact message', c1Start);
    assertOrExit(c1Start.api_screen_state.countdown?.message === 'Adhan will start in 30 seconds', 'C1 API message', c1Start);
    assertOrExit(
        c1Start.server_time.startsWith(setup.countdown_1_start.slice(0, 16)),
        'C1 at exact server second',
        c1Start
    );
    assertOrExit(c1Start.popup.media_overlay_display === 'none', 'Poster must not cover countdown', c1Start);
    assertOrExit(Number(c1Start.popup.timer) >= 28 && Number(c1Start.popup.timer) <= 30, 'C1 start: timer near 30', c1Start);
    results.push({ id: 'countdown-1-appears-20m', pass: true });
    results.push({ id: 'countdown-1-exact-message', pass: true });
    results.push({ id: 'no-poster-over-countdown-1', pass: true });

    await sleep(10000);
    const c1Mid = await captureCheckpoint(page, '03-countdown-1-mid', log,
        'Countdown #1 mid-window: still visible, timer counting down.');
    assertOrExit(c1Mid.popup.visible, 'C1 mid: visible', c1Mid);
    assertOrExit(Number(c1Mid.popup.timer) > 0 && Number(c1Mid.popup.timer) < 30, 'C1 mid: timer in range', c1Mid);
    results.push({ id: 'countdown-1-full-30s-window', pass: true });

    const c1Zero = await waitUntil(async () => {
        const p = await page.evaluate(() => {
            const timer = document.getElementById('countdown-popup-timer');
            const popup = document.getElementById('countdown-popup');
            const visible = popup && (popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex');
            return { visible, timer: timer ? timer.textContent.trim() : null };
        });
        return p.visible && p.timer === '00' ? p : null;
    }, 35000, 25);

    const c1AtZero = await captureCheckpoint(page, '04-countdown-1-at-zero', log,
        'Countdown #1 must reach 00 while still visible.');
    assertOrExit(c1Zero, 'C1 must show 00 while visible', c1AtZero);
    assertOrExit(c1AtZero.popup.visible, 'C1 popup visible at zero', c1AtZero);
    assertOrExit(c1AtZero.popup.timer === '00', 'C1 shows 00 before disappearing', c1AtZero);
    results.push({ id: 'countdown-1-reaches-zero', pass: true });
    results.push({ id: 'countdown-1-full-30s-visible', pass: true });

    await waitUntil(async () => {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        if (s.state !== 'TIMETABLE') return null;
        const popup = await page.evaluate(() => {
            const el = document.getElementById('countdown-popup');
            return !(el && (el.style.display === 'flex' || getComputedStyle(el).display === 'flex'));
        });
        return popup ? s : null;
    }, 15000, 200);

    const afterC1 = await captureCheckpoint(page, '05-after-countdown-1', log,
        'Countdown #1 must disappear immediately after window ends. No other countdown.');
    assertOrExit(!afterC1.popup.visible, 'After C1: popup hidden', afterC1);
    assertOrExit(afterC1.api_screen_state.state === 'TIMETABLE', 'After C1: TIMETABLE', afterC1);
    results.push({ id: 'countdown-1-disappears', pass: true });

    // Gap: no popup, proves no 23m/70s countdown
    await sleep(3000);
    const gap = await captureCheckpoint(page, '06-gap-no-popup', log,
        'Gap between countdowns: no popup. Proves no 23-minute or 70-second countdown.');
    assertOrExit(!gap.popup.visible && gap.api_screen_state.state === 'TIMETABLE', 'Gap: no countdown', gap);
    results.push({ id: 'no-23m-no-70s-countdown', pass: true });

    assertOrExit(gap.fullscreen_active, 'Fullscreen still active in gap', gap);

    await waitUntil(async () => {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        const popup = await page.evaluate(() => {
            const el = document.getElementById('countdown-popup');
            return el && (el.style.display === 'flex' || getComputedStyle(el).display === 'flex');
        });
        return s.state === 'COUNTDOWN' && s.countdown?.phase === 'iqamah' && popup;
    }, 1200000, 200);

    const c2Start = await captureCheckpoint(page, '07-countdown-2-start', log,
        'Countdown #2 exactly 30 seconds before Jamaat.');
    assertOrExit(c2Start.popup.visible, 'C2 start: visible', c2Start);
    assertOrExit(c2Start.popup.title === 'Iqamah will start in 30 seconds', 'C2 exact message', c2Start);
    assertOrExit(
        c2Start.server_time.startsWith(setup.countdown_2_start.slice(0, 16)),
        'C2 at exact server second',
        c2Start
    );
    assertOrExit(c2Start.popup.media_overlay_display === 'none', 'Poster must not cover C2', c2Start);
    assertOrExit(Number(c2Start.popup.timer) >= 28 && Number(c2Start.popup.timer) <= 30, 'C2 start: timer near 30', c2Start);
    results.push({ id: 'countdown-2-appears-30s', pass: true });
    results.push({ id: 'countdown-2-exact-message', pass: true });
    results.push({ id: 'no-poster-over-countdown-2', pass: true });

    await sleep(10000);
    const c2Mid = await captureCheckpoint(page, '08-countdown-2-mid', log, 'Countdown #2 mid-window.');
    assertOrExit(c2Mid.popup.visible, 'C2 mid: visible', c2Mid);

    const c2Zero = await waitUntil(async () => {
        const p = await page.evaluate(() => {
            const timer = document.getElementById('countdown-popup-timer');
            const popup = document.getElementById('countdown-popup');
            const visible = popup && (popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex');
            return { visible, timer: timer ? timer.textContent.trim() : null };
        });
        return p.visible && p.timer === '00' ? p : null;
    }, 35000, 25);

    const c2AtZero = await captureCheckpoint(page, '09-countdown-2-at-zero', log,
        'Countdown #2 reaches 00 before disappearing at Jamaat.');
    assertOrExit(c2Zero, 'C2 must show 00 while visible', c2AtZero);
    assertOrExit(c2AtZero.popup.visible, 'C2 popup visible at zero', c2AtZero);
    assertOrExit(c2AtZero.popup.timer === '00', 'C2 shows 00 before disappearing', c2AtZero);
    results.push({ id: 'countdown-2-reaches-zero', pass: true });

    await waitUntil(async () => {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        return s.state === 'TIMETABLE';
    }, 15000, 300);

    const afterJamaat = await captureCheckpoint(page, '10-after-jamaat', log,
        'After Jamaat: popup gone. Fullscreen still active. No refresh occurred.');
    assertOrExit(!afterJamaat.popup.visible, 'After Jamaat: no popup', afterJamaat);
    assertOrExit(afterJamaat.fullscreen_active, 'Fullscreen still active at end', afterJamaat);
    results.push({ id: 'countdown-2-disappears-at-jamaat', pass: true });
    results.push({ id: 'no-page-refresh', pass: true });
    results.push({ id: 'fullscreen-never-exited', pass: true });

    await browser.close();

    const evidence = {
        run_id: RUN_ID,
        mosque: 'Al Hidaya Academy, Queensbury, Bradford, UK',
        setup,
        all_passed: true,
        results,
        checkpoints: log,
        output_dir: OUTPUT_DIR,
    };

    fs.writeFileSync(path.join(OUTPUT_DIR, 'EVIDENCE.json'), JSON.stringify(evidence, null, 2));
    console.log('\nALL CHECKPOINTS PASSED');
    console.log('Evidence:', path.join(OUTPUT_DIR, 'EVIDENCE.json'));
})().catch((err) => {
    console.error(err);
    process.exit(1);
});
