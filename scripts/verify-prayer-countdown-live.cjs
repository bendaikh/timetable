const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { execSync } = require('child_process');

const ROOT = '/Users/fatimazahradarir/timetable';
const BASE_URL = 'http://127.0.0.1:8000';
const OUTPUT_DIR = path.join(ROOT, 'storage/app/verification/prayer-countdown');

function fetchScreenState(verifyTime) {
    const json = execSync(
        `curl -s -H "X-Verify-Time: ${verifyTime}" "${BASE_URL}/api/screen-state"`,
        { encoding: 'utf8' }
    );
    return JSON.parse(json);
}

const VERIFY_TZ_OFFSET = '+01:00';

function parseVerifyTime(verifyTime) {
    return new Date(`${verifyTime.replace(' ', 'T')}${VERIFY_TZ_OFFSET}`);
}

function formatVerifyTimeFromDate(date) {
    const parts = new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Europe/London',
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

function addSecondsToVerifyTime(verifyTime, seconds) {
    const next = parseVerifyTime(verifyTime);
    next.setSeconds(next.getSeconds() + seconds);
    return formatVerifyTimeFromDate(next);
}

async function readCountdownDom(page) {
    return page.evaluate(() => {
        const popup = document.getElementById('countdown-popup');
        const title = document.getElementById('countdown-popup-title');
        const timer = document.getElementById('countdown-popup-timer');
        const prayer = document.getElementById('countdown-popup-prayer');
        const overlay = document.getElementById('media-overlay');

        return {
            popupDisplay: popup ? getComputedStyle(popup).display : null,
            popupVisible: popup ? popup.style.display !== 'none' && getComputedStyle(popup).display !== 'none' : false,
            title: title ? title.textContent.trim() : null,
            timer: timer ? timer.textContent.trim() : null,
            prayer: prayer ? prayer.textContent.trim() : null,
            mediaOverlayDisplay: overlay ? getComputedStyle(overlay).display : null,
        };
    });
}

async function openPageAtTimeExpectHidden(page, verifyTime) {
    const clockState = { verifyTime };

    await page.clock.install({ time: parseVerifyTime(verifyTime) });
    await page.route('**/api/**', async (route) => {
        const headers = {
            ...route.request().headers(),
            'X-Verify-Time': clockState.verifyTime,
        };
        await route.continue({ headers });
    });

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForTimeout(4500);
}

async function openPageAtTime(page, verifyTime) {
    const clockState = { verifyTime };

    await page.clock.install({ time: parseVerifyTime(verifyTime) });

    await page.route('**/api/**', async (route) => {
        const headers = {
            ...route.request().headers(),
            'X-Verify-Time': clockState.verifyTime,
        };
        await route.continue({ headers });
    });

    page._advanceVerifyClock = async (seconds) => {
        await page.clock.fastForward(seconds * 1000);
        clockState.verifyTime = addSecondsToVerifyTime(clockState.verifyTime, seconds);
        await page.waitForTimeout(300);
    };

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForFunction(() => {
        const popup = document.getElementById('countdown-popup');
        if (!popup) {
            return false;
        }
        return popup.style.display === 'flex' || getComputedStyle(popup).display === 'flex';
    }, { timeout: 12000 }).catch(() => null);
    await page.waitForTimeout(500);
}

async function screenshot(page, name) {
    const filePath = path.join(OUTPUT_DIR, `${name}.png`);
    await page.screenshot({ path: filePath, fullPage: true });
    return filePath;
}

(async () => {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    const date = execSync('php scripts/setup-prayer-countdown-verification.php', {
        cwd: ROOT,
        encoding: 'utf8',
    }).trim();

    const results = [];
    const record = (id, pass, details = {}) => {
        results.push({ id, pass, ...details });
        console.log(`${pass ? 'PASS' : 'FAIL'} ${id}`, details);
    };

  // API sanity before browser
    const apiAt1240 = fetchScreenState(`${date} 12:40:00`);
    record('api-12:40:00-countdown', apiAt1240.state === 'COUNTDOWN' && apiAt1240.countdown?.phase === 'adhan', {
        state: apiAt1240.state,
        phase: apiAt1240.countdown?.phase,
        message: apiAt1240.countdown?.message,
        secondsRemaining: apiAt1240.countdown?.seconds_remaining,
    });

    const apiAt1237 = fetchScreenState(`${date} 12:37:00`);
    record('api-no-23min-trigger', apiAt1237.state === 'TIMETABLE', {
        state: apiAt1237.state,
        note: '12:37 is 23 minutes before iqamah — must not countdown',
    });

    const apiAt1245 = fetchScreenState(`${date} 12:45:00`);
    record('api-gap-12:45-timetable', apiAt1245.state === 'TIMETABLE', { state: apiAt1245.state });

    const apiAt125930 = fetchScreenState(`${date} 12:59:30`);
    record('api-12:59:30-iqamah-countdown', apiAt125930.state === 'COUNTDOWN' && apiAt125930.countdown?.phase === 'iqamah', {
        state: apiAt125930.state,
        phase: apiAt125930.countdown?.phase,
        message: apiAt125930.countdown?.message,
    });

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    // Scenario 1-3: Adhan countdown at 12:40:00
    await openPageAtTime(page, `${date} 12:40:00`);
    const dom124000 = await readCountdownDom(page);
    const shot124000 = await screenshot(page, '01-adhan-countdown-start-12-40-00');

    record('1-countdown-at-12-40-00', dom124000.popupVisible, { dom: dom124000, screenshot: shot124000 });
    record('2-adhan-message', dom124000.title === 'Adhan will start in 30 seconds', { title: dom124000.title });
    record('3-starts-at-30-not-70', Number(dom124000.timer) >= 28 && Number(dom124000.timer) <= 30, { timer: dom124000.timer });
    record('9-no-70-second-countdown', dom124000.timer !== '70' && dom124000.timer !== '69', { timer: dom124000.timer });

    await page._advanceVerifyClock(15);
    const dom124015 = await readCountdownDom(page);
    const shot124015 = await screenshot(page, '02-adhan-countdown-mid-12-40-15');
    record('3-countdown-midpoint-15', Number(dom124015.timer) >= 13 && Number(dom124015.timer) <= 15, { timer: dom124015.timer, screenshot: shot124015 });

    await page._advanceVerifyClock(14);
    const dom124029 = await readCountdownDom(page);
    const shot124029 = await screenshot(page, '03-adhan-countdown-near-end-12-40-29');
    record('3-countdown-near-end-1', Number(dom124029.timer) <= 2, { timer: dom124029.timer, screenshot: shot124029 });

    await page._advanceVerifyClock(1);
    await page.waitForTimeout(4000);
    const dom124030 = await readCountdownDom(page);
    const shot124030 = await screenshot(page, '04-adhan-countdown-ended-12-40-30');
    record('4-disappears-after-30-seconds', !dom124030.popupVisible, { dom: dom124030, screenshot: shot124030 });

    // Scenario 5: Gap period
    await page.unroute('**/api/**');
    await openPageAtTimeExpectHidden(page, `${date} 12:45:00`);
    const dom124500 = await readCountdownDom(page);
    const shot124500 = await screenshot(page, '05-gap-no-countdown-12-45-00');
    record('5-no-countdown-in-gap-12-45', !dom124500.popupVisible, { dom: dom124500, screenshot: shot124500 });

    await page.unroute('**/api/**');
    await openPageAtTimeExpectHidden(page, `${date} 12:59:29`);
    const dom125929 = await readCountdownDom(page);
    const shot125929 = await screenshot(page, '06-gap-no-countdown-12-59-29');
    record('5-no-countdown-at-12-59-29', !dom125929.popupVisible, { dom: dom125929, screenshot: shot125929 });

    // Scenario 6-8: Iqamah countdown
    await page.unroute('**/api/**');
    await openPageAtTime(page, `${date} 12:59:30`);
    const dom125930 = await readCountdownDom(page);
    const shot125930 = await screenshot(page, '07-iqamah-countdown-start-12-59-30');
    record('6-iqamah-countdown-at-12-59-30', dom125930.popupVisible, { dom: dom125930, screenshot: shot125930 });
    record('7-iqamah-message', dom125930.title === 'Iqamah will start in 30 seconds', { title: dom125930.title });
    record('8-iqamah-starts-at-30', Number(dom125930.timer) >= 28 && Number(dom125930.timer) <= 30, { timer: dom125930.timer });

    await page._advanceVerifyClock(28);
    const dom125959 = await readCountdownDom(page);
    const shot125959 = await screenshot(page, '08-iqamah-countdown-near-end-12-59-59');
    record('8-iqamah-near-end-1', Number(dom125959.timer) >= 1 && Number(dom125959.timer) <= 3, { timer: dom125959.timer, screenshot: shot125959 });

    await page._advanceVerifyClock(2);
    await page.waitForTimeout(4000);
    const dom130000 = await readCountdownDom(page);
    const shot130000 = await screenshot(page, '09-iqamah-countdown-ended-13-00-00');
    record('8-disappears-at-iqamah', !dom130000.popupVisible, { dom: dom130000, screenshot: shot130000 });

    // Scenario 10: 23 minutes before iqamah
    await page.unroute('**/api/**');
    await openPageAtTimeExpectHidden(page, `${date} 12:37:00`);
    const dom123700 = await readCountdownDom(page);
    const shot123700 = await screenshot(page, '10-no-countdown-23min-before-12-37-00');
    record('10-no-countdown-23min-before', !dom123700.popupVisible, { dom: dom123700, screenshot: shot123700 });

    // Scenario 11: Posters cannot cover countdown
    await page.unroute('**/api/**');
    await openPageAtTime(page, `${date} 12:40:00`);
    const domPoster = await readCountdownDom(page);
    const shotPoster = await screenshot(page, '11-countdown-above-posters-12-40-00');
    const posterBlocked = domPoster.popupVisible
        && (domPoster.mediaOverlayDisplay === 'none' || domPoster.mediaOverlayDisplay === '');
    record('11-posters-do-not-cover-countdown', posterBlocked, {
        dom: domPoster,
        apiState: apiAt1240.state,
        screenshot: shotPoster,
    });

    await browser.close();

    const report = {
        generatedAt: new Date().toISOString(),
        testDate: date,
        iqamahTime: `${date} 13:00:00`,
        outputDir: OUTPUT_DIR,
        allPassed: results.every((r) => r.pass),
        results,
        screenshots: fs.readdirSync(OUTPUT_DIR).filter((f) => f.endsWith('.png')).sort(),
    };

    const reportPath = path.join(OUTPUT_DIR, 'report.json');
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));

    const html = `<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Prayer Countdown Verification</title>
<style>body{font-family:Arial,sans-serif;max-width:1200px;margin:0 auto;padding:24px} .pass{color:#0a7a2f} .fail{color:#b00020}
.grid{display:grid;grid-template-columns:1fr;gap:24px} img{max-width:100%;border:1px solid #ccc;border-radius:8px}
.card{border:1px solid #ddd;border-radius:8px;padding:16px}</style></head><body>
<h1>Prayer Countdown Live Verification</h1>
<p>Test date: <strong>${report.testDate}</strong> | Iqamah: <strong>${report.iqamahTime}</strong> | Result: <strong class="${report.allPassed ? 'pass' : 'fail'}">${report.allPassed ? 'ALL PASSED' : 'SOME FAILED'}</strong></p>
<div class="grid">
${report.screenshots.map((file) => {
    const checks = report.results.filter((r) => r.screenshot && r.screenshot.endsWith(file));
    const status = checks.length ? checks.every((c) => c.pass) : true;
    return `<div class="card"><h2>${file}</h2><p class="${status ? 'pass' : 'fail'}">${status ? 'PASS' : 'FAIL'}</p><img src="${file}" alt="${file}"></div>`;
}).join('')}
</div></body></html>`;
    fs.writeFileSync(path.join(OUTPUT_DIR, 'report.html'), html);

    console.log('\n=== VERIFICATION REPORT ===');
    console.log(JSON.stringify(report, null, 2));
    process.exitCode = report.allPassed ? 0 : 1;
})();
