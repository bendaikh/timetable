const { chromium } = require('playwright');
const { execSync } = require('child_process');

function curlScreenConfig() {
    const json = execSync('curl -s http://127.0.0.1:8000/api/screen-config', { encoding: 'utf8' });
    return JSON.parse(json);
}

function updateCountdownFontSize(value) {
    execSync(
        `php artisan tinker --execute="
            \\$box = App\\\\Models\\\\BoxSetting::where('box_type', 'prayer_times_box')->first();
            \\$styling = \\$box->styling_settings ?? [];
            \\$styling['next_prayer_countdown_font_size'] = '${value}';
            \\$box->update(['styling_settings' => \\$styling]);
        "`,
        { cwd: '/Users/fatimazahradarir/timetable', encoding: 'utf8' }
    );
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForSelector('#next-prayer-countdown');
    await page.waitForTimeout(5000);

    const readDom = () => page.evaluate(() => {
        const node = document.getElementById('next-prayer-countdown');
        return {
            inline: node?.style.getPropertyValue('font-size') || null,
            priority: node?.style.getPropertyPriority('font-size') || null,
            computed: node ? window.getComputedStyle(node).fontSize : null,
        };
    });

    const before = await readDom();
    const apiBefore = curlScreenConfig();
    const beforeSize = apiBefore.box_settings?.prayer_times_box?.styling_settings?.next_prayer_countdown_font_size ?? '1.4';
    const nextSize = String(beforeSize) === '4' ? '2' : '4';
    updateCountdownFontSize(nextSize);
    const apiAfter = curlScreenConfig();

    await page.waitForTimeout(10000);

    const after = await readDom();
    const expected = `${nextSize}rem`;

    console.log(JSON.stringify({
        success: after.inline === expected && after.priority === 'important',
        before,
        after,
        expected,
        apiBeforeSize: beforeSize,
        apiAfterSize: apiAfter.box_settings?.prayer_times_box?.styling_settings?.next_prayer_countdown_font_size,
        configVersionChanged: apiBefore.config_version !== apiAfter.config_version,
    }, null, 2));

    await browser.close();
    process.exitCode = after.inline === expected && after.priority === 'important' ? 0 : 1;
})();
