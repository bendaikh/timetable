const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');
const { execSync } = require('child_process');

const ROOT = '/Users/fatimazahradarir/timetable';
const BASE_URL = process.env.AUDIT_BASE_URL || 'http://127.0.0.1:8000';
const OUTPUT_DIR = path.join(ROOT, 'storage/app/verification/countdown-stability');

function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
}

function fetchJson(url, verifyTime) {
    const header = verifyTime ? `-H "X-Verify-Time: ${verifyTime}"` : '';
    return JSON.parse(execSync(`curl -s ${header} "${url}"`, { encoding: 'utf8' }));
}

function offsetFromJamaat(jamaatIso, offsetSec) {
    const d = new Date(jamaatIso);
    d.setSeconds(d.getSeconds() + offsetSec);
    return d.toISOString().slice(0, 19).replace('T', ' ');
}

(async () => {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });

    const setup = JSON.parse(execSync('php scripts/setup-uk-manual-countdown-test.php', {
        cwd: ROOT,
        encoding: 'utf8',
    }));

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle', timeout: 60000 });
    await page.evaluate(() => document.documentElement.requestFullscreen?.().catch(() => null));

    const apiChecks = [];
    const negativeTimes = [
        { label: '23m-before', offsetSec: -1380, expect: 'TIMETABLE' },
        { label: '21m-before', offsetSec: -1260, expect: 'TIMETABLE' },
        { label: '19m-before', offsetSec: -1140, expect: 'TIMETABLE' },
        { label: '31s-before', offsetSec: -31, expect: 'TIMETABLE' },
    ];

    for (const check of negativeTimes) {
        const verifyTime = offsetFromJamaat(setup.jamaat_at, check.offsetSec);
        const state = fetchJson(`${BASE_URL}/api/screen-state`, verifyTime);
        apiChecks.push({
            ...check,
            verifyTime,
            state: state.state,
            pass: state.state === check.expect,
        });
    }

    // Wait for countdown #1 visible in browser
    const c1Start = Date.now();
    while (Date.now() - c1Start < 180000) {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        const popup = await page.evaluate(() => {
            const el = document.getElementById('countdown-popup');
            return el && (el.style.display === 'flex' || getComputedStyle(el).display === 'flex');
        });
        if (s.state === 'COUNTDOWN' && s.countdown?.phase === 'adhan' && popup) break;
        await sleep(300);
    }

    await sleep(35000);

    // Wait for countdown #2
    const c2Start = Date.now();
    while (Date.now() - c2Start < 1200000) {
        const s = fetchJson(`${BASE_URL}/api/screen-state`);
        const popup = await page.evaluate(() => {
            const el = document.getElementById('countdown-popup');
            return el && (el.style.display === 'flex' || getComputedStyle(el).display === 'flex');
        });
        if (s.state === 'COUNTDOWN' && s.countdown?.phase === 'iqamah' && popup) break;
        await sleep(300);
    }

    await sleep(35000);

    const verification = await page.evaluate(() => window.__countdownVerification || null);
    const diagnostic = fetchJson(`${BASE_URL}/admin/diagnostics/countdown`);

    const report = {
        generatedAt: new Date().toISOString(),
        setup,
        apiChecks,
        runtime: verification,
        server_timezone: diagnostic.log.server_timezone,
        stability: {
            instanceCount: verification?.instanceCount ?? null,
            duplicateRenderSkips: verification?.duplicateRenderSkips ?? null,
            pollSkipsSameSignature: verification?.pollSkipsSameSignature ?? null,
            driftAlertCount: verification?.driftAlerts?.length ?? null,
            stateTransitions: verification?.stateTransitions ?? [],
            tickCount: verification?.ticks?.length ?? null,
        },
        pass: false,
        failures: [],
    };

    if (verification?.instanceCount !== 2) {
        report.failures.push(`Expected 2 countdown instances (adhan+iqamah), got ${verification?.instanceCount}`);
    }
    if ((verification?.driftAlerts?.length || 0) > 0) {
        report.failures.push(`Timing drift alerts: ${verification.driftAlerts.length}`);
    }
    const transitions = verification?.stateTransitions || [];
    const hasTimetableToCountdown = transitions.some((t) => t.from !== 'COUNTDOWN' && t.to === 'COUNTDOWN');
    const hasCountdownToTimetable = transitions.some((t) => t.from === 'COUNTDOWN' && t.to === 'TIMETABLE');
    if (!hasTimetableToCountdown || !hasCountdownToTimetable) {
        report.failures.push('Missing clean TIMETABLE ↔ COUNTDOWN transitions');
    }
    if ((verification?.duplicateRenderSkips || 0) < 1 && (verification?.pollSkipsSameSignature || 0) < 5) {
        report.failures.push('Polling did not skip redundant countdown applies (stability guard inactive)');
    }
    const reachedZero = (verification?.ticks || []).some((t) => t.secondsRemaining === 0);
    if (!reachedZero) {
        report.failures.push('Timer never logged secondsRemaining 0');
    }

    report.pass = report.failures.length === 0;

    const outPath = path.join(OUTPUT_DIR, 'stability-report.json');
    fs.writeFileSync(outPath, JSON.stringify(report, null, 2));

    await browser.close();

    console.log(JSON.stringify(report, null, 2));
    process.exit(report.pass ? 0 : 1);
})().catch((err) => {
    console.error(err);
    process.exit(1);
});
