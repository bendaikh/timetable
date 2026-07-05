const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

async function capture(label, width, height, outDir) {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width, height } });
  await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(800);

  const metrics = await page.evaluate(() => {
    const prayerHeader = document.querySelector('.prayer-header');
    const announcementsHeader = document.querySelector('.announcements-header');

    function box(el) {
      if (!el) return null;
      const rect = el.getBoundingClientRect();
      const styles = getComputedStyle(el);
      return {
        top: Math.round(rect.top * 100) / 100,
        bottom: Math.round(rect.bottom * 100) / 100,
        height: Math.round(rect.height * 100) / 100,
        fontSize: styles.fontSize,
        paddingTop: styles.paddingTop,
        marginTop: styles.marginTop,
      };
    }

    const prayer = box(prayerHeader);
    const announcements = box(announcementsHeader);

    return {
      prayer,
      announcements,
      topDelta: prayer && announcements ? Math.round((prayer.top - announcements.top) * 100) / 100 : null,
      heightDelta: prayer && announcements ? Math.round((prayer.height - announcements.height) * 100) / 100 : null,
      bottomDelta: prayer && announcements ? Math.round((prayer.bottom - announcements.bottom) * 100) / 100 : null,
    };
  });

  const headerClip = await page.evaluate(() => {
    const prayerHeader = document.querySelector('.prayer-header');
    const announcementsHeader = document.querySelector('.announcements-header');
    if (!prayerHeader || !announcementsHeader) return null;
    const p = prayerHeader.getBoundingClientRect();
    const a = announcementsHeader.getBoundingClientRect();
    const top = Math.min(p.top, a.top) - 8;
    const left = Math.min(p.left, a.left) - 8;
    const right = Math.max(p.right, a.right) + 8;
    const bottom = Math.max(p.bottom, a.bottom) + 8;
    return {
      x: Math.max(0, left),
      y: Math.max(0, top),
      width: right - left,
      height: bottom - top,
    };
  });

  if (headerClip) {
    await page.screenshot({
      path: path.join(outDir, `${label}-headers.png`),
      clip: headerClip,
    });
  }

  await page.screenshot({
    path: path.join(outDir, `${label}-full.png`),
    fullPage: false,
  });

  await browser.close();
  return { label, width, height, metrics };
}

(async () => {
  const outDir = path.join(__dirname, '..', 'storage', 'app', 'header-alignment-proof');
  fs.mkdirSync(outDir, { recursive: true });

  const results = [];
  results.push(await capture('desktop', 1440, 900, outDir));
  results.push(await capture('tv', 3840, 2160, outDir));

  fs.writeFileSync(path.join(outDir, 'metrics.json'), JSON.stringify(results, null, 2));
  console.log(JSON.stringify(results, null, 2));
  console.log(`Screenshots saved to ${outDir}`);
})();
