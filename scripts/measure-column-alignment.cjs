const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2500);

  const metrics = await page.evaluate(() => {
    function info(el, name) {
      if (!el) return { name, missing: true };
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      return {
        name,
        top: Math.round(r.top * 100) / 100,
        marginTop: s.marginTop,
        paddingTop: s.paddingTop,
        borderTopWidth: s.borderTopWidth,
        alignSelf: s.alignSelf,
        alignItems: s.alignItems,
      };
    }

    const prayerSection = document.getElementById('prayer-times-section');
    const annSection = document.getElementById('announcements-section');
    const prayerHeader = document.querySelector('.prayer-header');
    const annHeader = document.querySelector('.announcements-header');
    const headerBox = document.getElementById('header-box');
    const prayerCol = document.getElementById('prayer-times-box');
    const annCol = document.querySelector('[data-box-root="announcements_box"]');

    return {
      headerBox: info(headerBox, 'header-box'),
      prayerCol: info(prayerCol, 'prayer-times-box'),
      annCol: info(annCol, 'announcements_box'),
      prayerSection: info(prayerSection, 'prayer-times-section'),
      annSection: info(annSection, 'announcements-section'),
      prayerHeader: info(prayerHeader, 'prayer-header'),
      annHeader: info(annHeader, 'announcements-header'),
      deltas: {
        columnTop: prayerCol && annCol ? prayerCol.getBoundingClientRect().top - annCol.getBoundingClientRect().top : null,
        sectionTop: prayerSection && annSection ? prayerSection.getBoundingClientRect().top - annSection.getBoundingClientRect().top : null,
        headerTop: prayerHeader && annHeader ? prayerHeader.getBoundingClientRect().top - annHeader.getBoundingClientRect().top : null,
        gapPrayer: prayerSection && headerBox ? prayerSection.getBoundingClientRect().top - headerBox.getBoundingClientRect().bottom : null,
        gapAnn: annSection && headerBox ? annSection.getBoundingClientRect().top - headerBox.getBoundingClientRect().bottom : null,
        gapPrayerHeaderFromSection: prayerSection && prayerHeader ? prayerHeader.getBoundingClientRect().top - prayerSection.getBoundingClientRect().top : null,
        gapAnnHeaderFromSection: annSection && annHeader ? annHeader.getBoundingClientRect().top - annSection.getBoundingClientRect().top : null,
      },
    };
  });

  const outDir = path.join(__dirname, '..', 'storage', 'app', 'header-alignment-proof');
  fs.mkdirSync(outDir, { recursive: true });
  await page.screenshot({ path: path.join(outDir, 'column-alignment-after-fix.png'), fullPage: false });
  fs.writeFileSync(path.join(outDir, 'column-alignment-metrics.json'), JSON.stringify(metrics, null, 2));
  console.log(JSON.stringify(metrics, null, 2));
  await browser.close();
})();
