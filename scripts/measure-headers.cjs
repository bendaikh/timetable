const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(500);

  const m = await page.evaluate(() => {
    const prayerSection = document.querySelector('.prayer-times-section');
    const annSection = document.querySelector('.announcements-section');
    const prayerHeader = document.querySelector('.prayer-header');
    const annHeader = document.querySelector('.announcements-header');

    function info(el, name) {
      if (!el) return { name, missing: true };
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        name,
        top: r.top,
        bottom: r.bottom,
        height: r.height,
        paddingTop: s.paddingTop,
        marginTop: s.marginTop,
        fontSize: s.fontSize,
      };
    }

    return {
      prayerSection: info(prayerSection, 'prayerSection'),
      annSection: info(annSection, 'annSection'),
      prayerHeader: info(prayerHeader, 'prayerHeader'),
      annHeader: info(annHeader, 'annHeader'),
      topDelta: prayerHeader && annHeader ? prayerHeader.getBoundingClientRect().top - annHeader.getBoundingClientRect().top : null,
      sectionTopDelta: prayerSection && annSection ? prayerSection.getBoundingClientRect().top - annSection.getBoundingClientRect().top : null,
    };
  });

  console.log(JSON.stringify(m, null, 2));
  await browser.close();
})();
