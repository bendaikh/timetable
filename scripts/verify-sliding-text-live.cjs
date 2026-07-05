const { chromium } = require('playwright');
const { execSync } = require('child_process');

function curlSlidingTexts() {
    const json = execSync('curl -s http://127.0.0.1:8000/api/sliding-texts', { encoding: 'utf8' });
    return JSON.parse(json);
}

function setDbFontSize(value) {
    execSync(
        `php artisan tinker --execute="App\\\\Models\\\\SlidingText::first()->update(['font_size' => ${value}]);"`,
        { cwd: '/Users/fatimazahradarir/timetable', encoding: 'utf8' }
    );
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });

    const slidingResponses = [];
    page.on('response', async (response) => {
        if (response.url().includes('/api/sliding-texts')) {
            try {
                slidingResponses.push(await response.json());
            } catch (_) {}
        }
    });

    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForSelector('[data-box-root="sliding_text_box"] .scroll-item');
    await page.waitForTimeout(5000);

    const hasApplyHelper = await page.evaluate(() => typeof applySlidingTextTypography === 'function');
    const hasImportantHelper = await page.evaluate(() => {
        const fn = window.applySlidingTextTypography ? window.applySlidingTextTypography.toString() : '';
        return fn.includes("setProperty('font-size'") && fn.includes('important');
    });

    const beforeDom = await page.evaluate(() => {
        const node = document.querySelector('[data-box-root="sliding_text_box"] .scroll-item');
        return {
            inline: node?.style.getPropertyValue('font-size') || null,
            priority: node?.style.getPropertyPriority('font-size') || null,
        };
    });

    const apiBefore = curlSlidingTexts();
    const nextSize = apiBefore.sliding_texts[0].font_size === 5 ? 6 : 5;
    setDbFontSize(nextSize);
    const apiAfter = curlSlidingTexts();

    await page.waitForTimeout(10000);

    const afterDom = await page.evaluate(() => {
        const node = document.querySelector('[data-box-root="sliding_text_box"] .scroll-item');
        return {
            inline: node?.style.getPropertyValue('font-size') || null,
            priority: node?.style.getPropertyPriority('font-size') || null,
        };
    });

    const signatures = await page.evaluate(() => ({
        lastApplied: window.lastAppliedSectionSignatures?.slidingTexts ?? null,
        lastReceived: window.lastReceivedVersions?.slidingTexts ?? null,
    }));

    console.log(JSON.stringify({
        hasApplyHelper,
        hasImportantHelper,
        beforeDom,
        afterDom,
        expected: `${nextSize}rem`,
        apiBeforeFont: apiBefore.sliding_texts[0].font_size,
        apiAfterFont: apiAfter.sliding_texts[0].font_size,
        apiBeforeVersion: apiBefore.sliding_texts_version,
        apiAfterVersion: apiAfter.sliding_texts_version,
        slidingPollCount: slidingResponses.length,
        lastSlidingResponse: slidingResponses[slidingResponses.length - 1] || null,
        signatures,
        success: afterDom.inline === `${nextSize}rem`,
    }, null, 2));

    await browser.close();
    process.exitCode = afterDom.inline === `${nextSize}rem` ? 0 : 1;
})();
