const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  await page.goto('http://127.0.0.1:8000/', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(3000);

  const outDir = path.join(__dirname, '..', 'storage', 'app', 'header-alignment-proof');
  fs.mkdirSync(outDir, { recursive: true });

  const capture = async (label) => {
    const data = await page.evaluate(() => {
      const header = document.getElementById('header-box');
      const gapY = header.getBoundingClientRect().bottom + 4;
      const spacer = document.querySelector('.board-header-content-gap');
      const board = document.getElementById('timetable-background-box');
      const hit = (x) => {
        const el = document.elementFromPoint(x, gapY);
        return el ? {
          tag: el.tagName,
          id: el.id || null,
          className: (el.className || '').toString().slice(0, 80),
          backgroundColor: getComputedStyle(el).backgroundColor,
        } : null;
      };
      return {
        gapY,
        boardBg: board ? getComputedStyle(board).backgroundColor : null,
        spacerBg: spacer ? getComputedStyle(spacer).backgroundColor : null,
        spacerHeight: spacer ? getComputedStyle(spacer).height : null,
        hitLeft: hit(200),
        hitCenter: hit(720),
        hitRight: hit(1200),
      };
    });
    return data;
  };

  const before = await capture('before');
  await page.screenshot({ path: path.join(outDir, 'gap-before-fix.png'), fullPage: false });

  // Simulate live config: board background changed to white while timetable box var was stale green
  await page.evaluate(() => {
    const board = document.getElementById('timetable-background-box');
    board.style.backgroundColor = '#ffffff';
    if (typeof syncBoardHeaderContentGap === 'function') {
      syncBoardHeaderContentGap();
    }
  });
  await page.waitForTimeout(200);

  const after = await capture('after');
  await page.screenshot({ path: path.join(outDir, 'gap-after-fix.png'), fullPage: false });

  const result = { before, after };
  fs.writeFileSync(path.join(outDir, 'gap-fix-verification.json'), JSON.stringify(result, null, 2));
  console.log(JSON.stringify(result, null, 2));
  await browser.close();
})();
