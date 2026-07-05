const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { execSync } = require('child_process');

const ROOT = '/Users/fatimazahradarir/timetable';
const BASE_URL = process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000';
const DATE = '2026-06-24';
const JAMAAT = `${DATE} 15:00:00`;
const OUTPUT_DIR = path.join(ROOT, 'storage/app/verification/prayer-countdown-audit');
const APP_TIMEZONE = 'Europe/London';

function londonOffsetForVerifyTime(verifyTime) {
    const probe = new Date(`${verifyTime.replace(' ', 'T')}Z`);
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: APP_TIMEZONE,
        timeZoneName: 'longOffset',
        hour: '2-digit',
    }).formatToParts(probe);
    const offset = parts.find((part) => part.type === 'timeZoneName')?.value || 'GMT';
    if (offset === 'GMT') {
        return '+00:00';
    }
    const match = offset.match(/GMT([+-]\d{1,2})(?::?(\d{2}))?/);
    if (!match) {
        return '+00:00';
    }
    const sign = match[1].startsWith('-') ? '-' : '+';
    const hours = String(Math.abs(Number(match[1]))).padStart(2, '0');
    const minutes = match[2] || '00';
    return `${sign}${hours}:${minutes}`;
}

function parseTime(value) {
    const offset = londonOffsetForVerifyTime(value);
    return new Date(`${value.replace(' ', 'T')}${offset}`);
}

function formatTime(date) {
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: APP_TIMEZONE,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    }).formatToParts(date);

    const get = (type) => parts.find((part) => part.type === type)?.value;
    return `${get('year')}-${get('month')}-${get('day')} ${get('hour')}:${get('minute')}:${get('second')}`;
}

function addSeconds(value, seconds) {
    const next = parseTime(value);
    next.setSeconds(next.getSeconds() + seconds);
    return formatTime(next);
}

function fetchScreenState(verifyTime) {
    const json = execSync(
        `curl -s -H "X-Verify-Time: ${verifyTime}" "${BASE_URL}/api/screen-state"`,
        { encoding: 'utf8' }
    );
    return JSON.parse(json);
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

async function screenshot(page, name) {
    const filePath = path.join(OUTPUT_DIR, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage: true });
    return filePath;
}

function record(results, id, pass, details = {}) {
    const entry = { id, pass, ...details };
    results.push(entry);
    console.log(`${pass ? 'PASS' : 'FAIL'} ${id}`, details);
    return entry;
}

(async () => {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    execSync('php scripts/setup-prayer-countdown-audit.php', { cwd: ROOT, encoding: 'utf8' });

    const results = [];
    const apiChecks = [
        ['api-20m01-before', addSeconds(JAMAAT, -1201), 'TIMETABLE'],
        ['api-20m00-exact', addSeconds(JAMAAT, -1200), 'COUNTDOWN', 'adhan'],
        ['api-20m15-during', addSeconds(JAMAAT, -1185), 'COUNTDOWN', 'adhan'],
        ['api-20m30-after', addSeconds(JAMAAT, -1170), 'TIMETABLE'],
        ['api-gap-19m29', addSeconds(JAMAAT, -1169), 'TIMETABLE'],
        ['api-gap-31s', addSeconds(JAMAAT, -31), 'TIMETABLE'],
        ['api-30s-exact', addSeconds(JAMAAT, -30), 'COUNTDOWN', 'iqamah'],
        ['api-15s-during', addSeconds(JAMAAT, -15), 'COUNTDOWN', 'iqamah'],
        ['api-at-jamaat', JAMAAT, 'TIMETABLE'],
        ['api-after-jamaat', addSeconds(JAMAAT, 1), 'TIMETABLE'],
        ['api-23m-before', addSeconds(JAMAAT, -1380), 'TIMETABLE'],
        ['api-21m-before', addSeconds(JAMAAT, -1260), 'TIMETABLE'],
        ['api-19m-before', addSeconds(JAMAAT, -1140), 'TIMETABLE'],
    ];

    for (const [id, verifyTime, expectedState, expectedPhase] of apiChecks) {
        const payload = fetchScreenState(verifyTime);
        const pass = expectedPhase
            ? payload.state === expectedState && payload.countdown?.phase === expectedPhase
            : payload.state === expectedState;
        record(results, id, pass, {
            verifyTime,
            state: payload.state,
            phase: payload.countdown?.phase ?? null,
            message: payload.countdown?.message ?? null,
            secondsRemaining: payload.countdown?.seconds_remaining ?? null,
        });
    }

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1440, height: 900 },
        recordVideo: { dir: OUTPUT_DIR, size: { width: 1440, height: 900 } },
    });
    await context.tracing.start({ screenshots: true, snapshots: true, sources: true });
    const page = await context.newPage();

    let verifyTime = addSeconds(JAMAAT, -1201);
    const clockState = { verifyTime };

    await page.clock.install({ time: parseTime(verifyTime) });
    await page.route('**/api/**', async (route) => {
        const headers = {
            ...route.request().headers(),
            'X-Verify-Time': clockState.verifyTime,
        };
        await route.continue({ headers });
    });

    const advanceTo = async (nextTime, label) => {
        const previous = parseTime(clockState.verifyTime).getTime();
        const target = parseTime(nextTime).getTime();
        const deltaMs = Math.max(0, target - previous);

        clockState.verifyTime = nextTime;
        if (deltaMs > 0) {
            await page.clock.fastForward(deltaMs);
        }
        await page.waitForTimeout(1200);
        const popup = await readPopup(page);
        const api = fetchScreenState(nextTime);
        await screenshot(page, label);
        return { popup, api };
    };

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(2000);

    const before = await advanceTo(addSeconds(JAMAAT, -1201), '01-before-countdown-1');
    record(results, 'browser-before-countdown-1', !before.popup.visible && before.api.state === 'TIMETABLE', before);

    const start1 = await advanceTo(addSeconds(JAMAAT, -1200), '02-start-countdown-1');
    record(results, 'browser-start-countdown-1', start1.popup.visible
        && start1.popup.title === 'Adhan will start in 30 seconds'
        && Number(start1.popup.timer) > 0
        && start1.api.countdown?.seconds_remaining === 30, start1);

    const mid1 = await advanceTo(addSeconds(JAMAAT, -1185), '03-mid-countdown-1');
    record(results, 'browser-mid-countdown-1', mid1.popup.visible
        && mid1.popup.title === 'Adhan will start in 30 seconds'
        && Number(mid1.popup.timer) > 0
        && mid1.api.countdown?.seconds_remaining === 15, mid1);

    const end1 = await advanceTo(addSeconds(JAMAAT, -1171), '04-end-countdown-1');
    record(results, 'browser-end-countdown-1', end1.popup.visible
        && Number(end1.popup.timer) <= 1, end1);

    const after1 = await advanceTo(addSeconds(JAMAAT, -1170), '05-after-countdown-1');
    record(results, 'browser-after-countdown-1', !after1.popup.visible && after1.api.state === 'TIMETABLE', after1);

    const gap = await advanceTo(addSeconds(JAMAAT, -600), '06-gap-middle');
    record(results, 'browser-gap-middle', !gap.popup.visible && gap.api.state === 'TIMETABLE', gap);

    const before2 = await advanceTo(addSeconds(JAMAAT, -31), '07-before-countdown-2');
    record(results, 'browser-before-countdown-2', !before2.popup.visible && before2.api.state === 'TIMETABLE', before2);

    const start2 = await advanceTo(addSeconds(JAMAAT, -30), '08-start-countdown-2');
    record(results, 'browser-start-countdown-2', start2.popup.visible
        && start2.popup.title === 'Iqamah will start in 30 seconds'
        && Number(start2.popup.timer) > 0
        && start2.api.countdown?.seconds_remaining === 30, start2);

    const mid2 = await advanceTo(addSeconds(JAMAAT, -15), '09-mid-countdown-2');
    record(results, 'browser-mid-countdown-2', mid2.popup.visible
        && mid2.popup.title === 'Iqamah will start in 30 seconds'
        && Number(mid2.popup.timer) > 0
        && mid2.api.countdown?.seconds_remaining === 15, mid2);

    const end2 = await advanceTo(addSeconds(JAMAAT, -1), '10-end-countdown-2');
    record(results, 'browser-end-countdown-2', end2.popup.visible
        && Number(end2.popup.timer) <= 1, end2);

    const atJamaat = await advanceTo(JAMAAT, '11-at-jamaat');
    record(results, 'browser-at-jamaat', !atJamaat.popup.visible && atJamaat.api.state === 'TIMETABLE', atJamaat);

    const afterJamaat = await advanceTo(addSeconds(JAMAAT, 5), '12-after-jamaat');
    record(results, 'browser-after-jamaat', !afterJamaat.popup.visible && afterJamaat.api.state === 'TIMETABLE', afterJamaat);

    // Stale response race: delayed TIMETABLE must not cancel active countdown.
    const racePage = await context.newPage();
    let delayedTimetable = false;
    let raceVerifyTime = addSeconds(JAMAAT, -1185);
    await racePage.clock.install({ time: parseTime(raceVerifyTime) });
    await racePage.route('**/api/**', async (route) => {
        if (delayedTimetable) {
            delayedTimetable = false;
            await new Promise((resolve) => setTimeout(resolve, 2500));
            return route.fulfill({
                status: 200,
                contentType: 'application/json',
                body: JSON.stringify({
                    state: 'TIMETABLE',
                    timestamp: '2026-06-24T13:39:59+01:00',
                    app_timezone: APP_TIMEZONE,
                }),
            });
        }

        const headers = {
            ...route.request().headers(),
            'X-Verify-Time': raceVerifyTime,
        };
        await route.continue({ headers });
    });
    await racePage.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    raceVerifyTime = addSeconds(JAMAAT, -1200);
    await racePage.clock.fastForward(parseTime(raceVerifyTime).getTime() - parseTime(addSeconds(JAMAAT, -1201)).getTime());
    delayedTimetable = true;
    await racePage.waitForTimeout(5000);
    const racePopup = await readPopup(racePage);
    record(results, 'browser-stale-response-race', racePopup.visible
        && racePopup.title === 'Adhan will start in 30 seconds', racePopup);
    await screenshot(racePage, '13-stale-response-race');

    const tracePath = path.join(OUTPUT_DIR, 'audit-trace.zip');
    await context.tracing.stop({ path: tracePath });
    await context.close();
    await browser.close();

    const summary = {
        generatedAt: new Date().toISOString(),
        jamaat: JAMAAT,
        total: results.length,
        passed: results.filter((result) => result.pass).length,
        failed: results.filter((result) => !result.pass).length,
        results,
        artifacts: {
            outputDir: OUTPUT_DIR,
            trace: tracePath,
            videoDir: OUTPUT_DIR,
        },
    };

    const summaryPath = path.join(OUTPUT_DIR, 'audit-summary.json');
    fs.writeFileSync(summaryPath, JSON.stringify(summary, null, 2));

    console.log('\nAudit summary:', summaryPath);
    console.log(`Passed ${summary.passed}/${summary.total}`);

    if (summary.failed > 0) {
        process.exit(1);
    }
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
