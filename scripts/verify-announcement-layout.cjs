const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const OUT_DIR = path.join(__dirname, '..', 'storage', 'app', 'announcement-layout-proof');
const BASE_URL = process.env.TIMETABLE_URL || 'http://127.0.0.1:8000/';

async function measureAnnouncements(page) {
  return page.evaluate(() => {
    const content = document.getElementById('announcements-content');
    const items = Array.from(document.querySelectorAll('#announcements-content .announcement-item'));
    const visibleItems = items.filter((el) => {
      const style = window.getComputedStyle(el);
      return style.display !== 'none' && style.visibility !== 'hidden' && el.offsetHeight > 0;
    });

    function rect(el) {
      if (!el) return null;
      const r = el.getBoundingClientRect();
      return {
        top: Math.round(r.top),
        bottom: Math.round(r.bottom),
        height: Math.round(r.height),
      };
    }

    const header = document.querySelector('.announcements-header');
    const section = document.getElementById('announcements-section');
    const active = document.querySelector('.announcement-item.is-active');
    const containers = items.map((item) => {
      const container = item.querySelector('.announcement-text-container');
      const scroll = item.querySelector('.announcement-text-scroll');
      return {
        id: item.dataset.announcementId,
        visible: visibleItems.includes(item),
        itemHeight: item.offsetHeight,
        containerClientHeight: container ? container.clientHeight : 0,
        scrollHeight: scroll ? scroll.scrollHeight : 0,
        clipped: container && scroll ? scroll.scrollHeight > container.clientHeight + 2 : false,
      };
    });

    return {
      mode: content ? content.dataset.displayMode : null,
      sectionHeight: section ? section.clientHeight : 0,
      contentHeight: content ? content.clientHeight : 0,
      header: rect(header),
      content: rect(content),
      totalItems: items.length,
      visibleItemCount: visibleItems.length,
      headerAboveContent: header && content
        ? content.getBoundingClientRect().top >= header.getBoundingClientRect().bottom - 1
        : null,
      containers,
      activeContainerHeight: active
        ? (active.querySelector('.announcement-text-container') || {}).clientHeight
        : null,
    };
  });
}

async function runScenario(browser, name, setup) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto(BASE_URL, { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForSelector('#announcements-content', { timeout: 15000 });
  await setup(page);
  await page.waitForTimeout(2000);

  const metrics = await measureAnnouncements(page);
  const screenshotPath = path.join(OUT_DIR, `${name}.png`);
  await page.screenshot({ path: screenshotPath });

  await context.close();
  return { name, screenshotPath, metrics };
}

(async () => {
  fs.mkdirSync(OUT_DIR, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const results = [];

  results.push(await runScenario(browser, 'rotation-mode', async (page) => {
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'rotation');
      window.dispatchEvent(new StorageEvent('storage', {
        key: 'announcementDisplayMode',
        newValue: 'rotation',
      }));
    });
    await page.waitForTimeout(500);
  }));

  results.push(await runScenario(browser, 'show-all-mode', async (page) => {
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'show-all');
      window.dispatchEvent(new StorageEvent('storage', {
        key: 'announcementDisplayMode',
        newValue: 'show-all',
      }));
    });
    await page.waitForTimeout(500);
  }));

  results.push(await runScenario(browser, 'rotation-short-viewport', async (page) => {
    await page.setViewportSize({ width: 1440, height: 600 });
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'rotation');
      window.dispatchEvent(new StorageEvent('storage', {
        key: 'announcementDisplayMode',
        newValue: 'rotation',
      }));
    });
    await page.waitForTimeout(500);
  }));

  await browser.close();

  const reportPath = path.join(OUT_DIR, 'report.json');
  fs.writeFileSync(reportPath, JSON.stringify(results, null, 2));
  console.log(JSON.stringify(results, null, 2));
  console.log(`\nScreenshots saved to ${OUT_DIR}`);
})();
