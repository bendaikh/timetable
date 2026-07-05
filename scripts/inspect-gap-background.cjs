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
  await page.screenshot({ path: path.join(outDir, 'gap-before-fix.png'), fullPage: false });

  const report = await page.evaluate(() => {
    function rgb(c) {
      return c || 'none';
    }

    function inspectEl(el, label) {
      if (!el) return { label, missing: true };
      const r = el.getBoundingClientRect();
      const s = getComputedStyle(el);
      const before = getComputedStyle(el, '::before');
      const after = getComputedStyle(el, '::after');
      return {
        label,
        tag: el.tagName,
        id: el.id || null,
        className: el.className || null,
        top: Math.round(r.top * 100) / 100,
        bottom: Math.round(r.bottom * 100) / 100,
        height: Math.round(r.height * 100) / 100,
        backgroundColor: rgb(s.backgroundColor),
        backgroundImage: s.backgroundImage,
        opacity: s.opacity,
        zIndex: s.zIndex,
        position: s.position,
        overflow: s.overflow,
        marginTop: s.marginTop,
        paddingTop: s.paddingTop,
        cssVarBoardPageBg: s.getPropertyValue('--board-page-background').trim(),
        cssVarDisplayBg: s.getPropertyValue('--display-background-color').trim(),
        pseudoBefore: {
          content: before.content,
          display: before.display,
          height: before.height,
          backgroundColor: rgb(before.backgroundColor),
          position: before.position,
          zIndex: before.zIndex,
        },
        pseudoAfter: {
          content: after.content,
          display: after.display,
          backgroundColor: rgb(after.backgroundColor),
        },
      };
    }

    const header = document.getElementById('header-box');
    const main = document.querySelector('.board-main-content');
    const row = main?.querySelector('.row');
    const prayerCol = document.getElementById('prayer-times-box');
    const annCol = document.querySelector('[data-box-root="announcements_box"]');
    const prayerSec = document.getElementById('prayer-times-section');
    const annSec = document.getElementById('announcements-section');
    const board = document.getElementById('timetable-background-box');
    const unified = document.querySelector('.unified-container');

    const headerBottom = header.getBoundingClientRect().bottom;
    const gapY = headerBottom + 4;
    const xs = [200, 720, 1200];

    const hitTest = xs.map((x) => {
      const stack = [];
      let el = document.elementFromPoint(x, gapY);
      const seen = new Set();
      while (el && !seen.has(el)) {
        seen.add(el);
        const s = getComputedStyle(el);
        stack.push({
          tag: el.tagName,
          id: el.id || null,
          className: (el.className || '').toString().slice(0, 80),
          backgroundColor: rgb(s.backgroundColor),
          zIndex: s.zIndex,
          position: s.position,
        });
        el = el.parentElement;
      }
      return { x, y: gapY, stack };
    });

    const mainBefore = getComputedStyle(main, '::before');
    const mainBeforeRect = (() => {
      // approximate ::before box: same width as main, height from computed, at main top
      const mr = main.getBoundingClientRect();
      return {
        top: mr.top,
        bottom: mr.top + parseFloat(mainBefore.height || '0'),
        height: mainBefore.height,
        backgroundColor: rgb(mainBefore.backgroundColor),
      };
    })();

    return {
      gapY,
      headerBottom,
      mainTop: main.getBoundingClientRect().top,
      rowTop: row?.getBoundingClientRect().top,
      hitTest,
      mainBeforeRect,
      elements: [
        inspectEl(board, 'timetable-background-box'),
        inspectEl(unified, 'unified-container'),
        inspectEl(header, 'header-box'),
        inspectEl(main, 'board-main-content'),
        inspectEl(row, 'row'),
        inspectEl(prayerCol, 'prayer-times-box'),
        inspectEl(annCol, 'announcements_box'),
        inspectEl(prayerSec, 'prayer-times-section'),
        inspectEl(annSec, 'announcements-section'),
      ],
    };
  });

  fs.writeFileSync(path.join(outDir, 'gap-inspection-report.json'), JSON.stringify(report, null, 2));
  console.log(JSON.stringify(report, null, 2));
  await browser.close();
})();
