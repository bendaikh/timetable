const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { execSync } = require('child_process');

const ROOT = '/Users/fatimazahradarir/timetable';
const BASE_URL = process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000';
const OUTPUT_DIR = path.join(ROOT, 'storage/app/verification/uk-manual-countdown');

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function fetchJson(url) {
    return JSON.parse(execSync(`curl -s "${url}"`, { encoding: 'utf8' }));
}

async function readPopup(page) {
    return page.evaluate(() => {
        const popup = document.getElementById('countdown-popup');
        const title = document.getElementById('countdown-popup-title');
        const timer = document.getElementById('countdown-popup-timer');
        const prayer = document.getElementById('countdown-popup-prayer');

        return {
            visible: popup
                ? popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex'
                : false,
            title: title ? title.textContent.trim() : null,
            timer: timer ? timer.textContent.trim() : null,
            prayer: prayer ? prayer.textContent.trim() : null,
        };
    });
}

async function screenshot(page, name, log, extra = {}) {
    const filePath = path.join(OUTPUT_DIR, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage: true });
    const diagnostic = fetchJson(`${BASE_URL}/admin/diagnostics/countdown`);
    const screenState = fetchJson(`${BASE_URL}/api/screen-state`);

    const entry = {
        screenshot: filePath,
        captured_at: new Date().toISOString(),
        server_time: diagnostic.log.server_time,
        server_timezone: diagnostic.log.server_timezone,
        app_timezone: screenState.app_timezone || null,
        screen_state: screenState.state,
        countdown_phase: screenState.countdown?.phase || null,
        countdown_message: screenState.countdown?.message || null,
        seconds_remaining: screenState.countdown?.seconds_remaining ?? null,
        popup: await readPopup(page),
        ...extra,
    };
    log.push(entry);
    console.log(`[${entry.captured_at}] ${name}`, {
        server_time: entry.server_time,
        popup: entry.popup,
        state: entry.screen_state,
    });
    return entry;
}

async function waitUntil(condition, timeoutMs, intervalMs = 1000) {
    const start = Date.now();
    while (Date.now() - start < timeoutMs) {
        const value = await condition();
        if (value) {
            return value;
        }
        await sleep(intervalMs);
    }
    return null;
}

(async () => {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    const setup = JSON.parse(execSync('php scripts/setup-uk-manual-countdown-test.php', {
        cwd: ROOT,
        encoding: 'utf8',
    }));

    const log = [];
    const results = [];

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        recordVideo: { dir: OUTPUT_DIR, size: { width: 1440, height: 900 } },
    });
    await context.tracing.start({ screenshots: true, snapshots: true, sources: true });
    const page = await context.newPage();

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.evaluate(() => {
        const el = document.documentElement;
        if (el.requestFullscreen) {
            return el.requestFullscreen().catch(() => null);
        }
        return null;
    }).catch(() => null);

    await screenshot(page, '00-page-loaded-fullscreen', log, { setup });

    const beforeCountdown1 = await waitUntil(async () => {
        const popup = await readPopup(page);
        const state = fetchJson(`${BASE_URL}/api/screen-state`);
        if (!popup.visible && state.state === 'TIMETABLE') {
            return { popup, state };
        }
        return null;
    }, 150000, 2000);

    await screenshot(page, '01-before-countdown-1', log, {
        note: 'Should be TIMETABLE with no popup before Jamaat-20m',
        wait: beforeCountdown1,
    });

    const countdown1Start = await waitUntil(async () => {
        const popup = await readPopup(page);
        const state = fetchJson(`${BASE_URL}/api/screen-state`);
        if (popup.visible && popup.title === 'Adhan will start in 30 seconds' && state.state === 'COUNTDOWN') {
            return { popup, state };
        }
        return null;
    }, 180000, 500);

    results.push({
        id: 'countdown-1-appears',
        pass: !!countdown1Start,
        expected: 'Adhan will start in 30 seconds at Jamaat-20m',
        setup,
    });
    await screenshot(page, '02-countdown-1-start', log, { event: countdown1Start });

    const countdown1Mid = await waitUntil(async () => {
        const popup = await readPopup(page);
        if (popup.visible && Number(popup.timer) > 0 && Number(popup.timer) <= 20) {
            return popup;
        }
        return null;
    }, 25000, 250);

    results.push({
        id: 'countdown-1-mid-timer',
        pass: !!countdown1Mid && Number(countdown1Mid.timer) > 0,
    });
    await screenshot(page, '03-countdown-1-mid', log, { event: countdown1Mid });

    const countdown1End = await waitUntil(async () => {
        const popup = await readPopup(page);
        if (popup.visible && Number(popup.timer) <= 1) {
            return popup;
        }
        return null;
    }, 35000, 100);

    results.push({
        id: 'countdown-1-reaches-zero',
        pass: !!countdown1End,
    });
    await screenshot(page, '04-countdown-1-at-zero', log, { event: countdown1End });

    const afterCountdown1 = await waitUntil(async () => {
        const popup = await readPopup(page);
        const state = fetchJson(`${BASE_URL}/api/screen-state`);
        if (!popup.visible && state.state === 'TIMETABLE') {
            return { popup, state };
        }
        return null;
    }, 15000, 500);

    results.push({
        id: 'countdown-1-disappears',
        pass: !!afterCountdown1,
    });
    await screenshot(page, '05-after-countdown-1', log, { event: afterCountdown1 });

    await screenshot(page, '06-gap-period', log, {
        note: 'Gap between countdown #1 end and countdown #2 start',
    });

    const countdown2Start = await waitUntil(async () => {
        const popup = await readPopup(page);
        const state = fetchJson(`${BASE_URL}/api/screen-state`);
        if (popup.visible && popup.title === 'Iqamah will start in 30 seconds' && state.state === 'COUNTDOWN') {
            return { popup, state };
        }
        return null;
    }, 1200000, 500);

    results.push({
        id: 'countdown-2-appears',
        pass: !!countdown2Start,
        expected: 'Iqamah will start in 30 seconds at Jamaat-30s',
    });
    await screenshot(page, '07-countdown-2-start', log, { event: countdown2Start });

    const countdown2Mid = await waitUntil(async () => {
        const popup = await readPopup(page);
        if (popup.visible && Number(popup.timer) > 0 && Number(popup.timer) <= 20) {
            return popup;
        }
        return null;
    }, 25000, 250);

    results.push({
        id: 'countdown-2-mid-timer',
        pass: !!countdown2Mid && Number(countdown2Mid.timer) > 0,
    });
    await screenshot(page, '08-countdown-2-mid', log, { event: countdown2Mid });

    const countdown2End = await waitUntil(async () => {
        const popup = await readPopup(page);
        if (popup.visible && Number(popup.timer) <= 1) {
            return popup;
        }
        return null;
    }, 35000, 100);

    results.push({
        id: 'countdown-2-reaches-zero',
        pass: !!countdown2End,
    });
    await screenshot(page, '09-countdown-2-at-zero', log, { event: countdown2End });

    const afterJamaat = await waitUntil(async () => {
        const popup = await readPopup(page);
        const state = fetchJson(`${BASE_URL}/api/screen-state`);
        if (!popup.visible && state.state === 'TIMETABLE') {
            return { popup, state };
        }
        return null;
    }, 15000, 500);

    results.push({
        id: 'countdown-2-disappears-at-jamaat',
        pass: !!afterJamaat,
    });
    await screenshot(page, '10-after-jamaat', log, { event: afterJamaat });

    const tracePath = path.join(OUTPUT_DIR, 'uk-manual-trace.zip');
    await context.tracing.stop({ path: tracePath });
    await context.close();
    await browser.close();

    const summary = {
        generatedAt: new Date().toISOString(),
        mosque: 'Al Hidaya Academy, Queensbury, Bradford, UK',
        timezone: setup.timezone,
        setup,
        results,
        passed: results.filter((r) => r.pass).length,
        failed: results.filter((r) => !r.pass).length,
        log,
        artifacts: {
            outputDir: OUTPUT_DIR,
            trace: tracePath,
        },
    };

    const summaryPath = path.join(OUTPUT_DIR, 'manual-test-summary.json');
    fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2));

    console.log('\nManual UK test summary:', summaryPath);
    console.log(`Passed ${summary.passed}/${results.length}`);

    if (summary.failed > 0) {
        process.exit(1);
    }
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
