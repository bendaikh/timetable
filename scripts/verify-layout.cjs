const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(800);

  const metrics = await page.evaluate(() => {
    const prayerCol = document.querySelector('[data-box-root="prayer_times_box"]');
    const annCol = document.querySelector('[data-box-root="announcements_box"]');
    const annSection = document.querySelector('.announcements-section');
    const annHeader = document.querySelector('.announcements-header');

    function rect(el) {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return { width: Math.round(r.width), height: Math.round(r.height), left: Math.round(r.left), top: Math.round(r.top) };
    }

    return {
      prayerCol: rect(prayerCol),
      annCol: rect(annCol),
      annSection: rect(annSection),
      annHeader: rect(annHeader),
      viewport: window.innerWidth,
    };
  });

  await page.screenshot({ path: 'storage/app/header-alignment-proof/after-revert-full.png' });
  console.log(JSON.stringify(metrics, null, 2));
  await browser.close();
})();
