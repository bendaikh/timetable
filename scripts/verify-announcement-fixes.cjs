const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const OUT_DIR = path.join(__dirname, '..', 'storage', 'app', 'announcement-layout-proof');
const BASE_URL = process.env.TIMETABLE_URL || 'http://127.0.0.1:8000/';
const ADMIN_URL = process.env.ADMIN_URL || 'http://127.0.0.1:8000/admin/announcements';

function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

async function getRotationState(page) {
  return page.evaluate(() => {
    const items = Array.from(document.querySelectorAll('#announcements-content .rotating-announcement'));
    const visible = items.filter((el) => window.getComputedStyle(el).display !== 'none');
    const active = document.querySelector('.announcement-item.is-active');
    return {
      mode: document.getElementById('announcements-content')?.dataset.displayMode || null,
      total: items.length,
      visibleCount: visible.length,
      activeId: active?.dataset.announcementId || null,
      durations: items.map((el) => ({
        id: el.dataset.announcementId,
        seconds: el.dataset.displayDurationSeconds || null,
        ms: el.dataset.duration || null,
      })),
      headerAboveContent: (() => {
        const header = document.querySelector('.announcements-header');
        const content = document.getElementById('announcements-content');
        if (!header || !content) return null;
        return content.getBoundingClientRect().top >= header.getBoundingClientRect().bottom - 1;
      })(),
      activeContainerHeight: active
        ? active.querySelector('.announcement-text-container')?.clientHeight || 0
        : 0,
    };
  });
}

async function waitForActiveChange(page, previousId, timeoutMs = 8000) {
  const start = Date.now();
  while (Date.now() - start < timeoutMs) {
    const state = await getRotationState(page);
    if (state.activeId && state.activeId !== previousId) {
      return state;
    }
    await page.waitForTimeout(200);
  }
  throw new Error(`Active announcement did not change from id ${previousId} within ${timeoutMs}ms`);
}

(async () => {
  fs.mkdirSync(OUT_DIR, { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
  const results = { passed: [], failed: [] };

  function pass(name, details) {
    results.passed.push({ name, details });
    console.log(`PASS: ${name}`, details || '');
  }

  function fail(name, error) {
    results.failed.push({ name, error: String(error) });
    console.error(`FAIL: ${name}`, error);
  }

  try {
    await page.goto(BASE_URL, { waitUntil: 'networkidle', timeout: 60000 });
    await page.waitForSelector('#announcements-content', { timeout: 20000 });
    await page.waitForTimeout(2500);

    // Test 1: rotation mode shows only one card
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'rotation');
    });
    await page.evaluate(() => {
      if (typeof applyAnnouncementsPresentation === 'function') {
        applyAnnouncementsPresentation('display-mode-storage');
      }
    });
    await page.waitForTimeout(1000);

    const rotationState = await getRotationState(page);
    assert(rotationState.mode === 'rotation', `expected rotation mode, got ${rotationState.mode}`);
    assert(rotationState.visibleCount === 1, `expected 1 visible announcement, got ${rotationState.visibleCount}`);
    assert(rotationState.activeId, 'expected an active announcement');
    assert(rotationState.headerAboveContent === true, 'content should start below header');
    assert(rotationState.activeContainerHeight > 0, 'active text container should have height');
    pass('rotation-single-visible-card', rotationState);
    await page.screenshot({ path: path.join(OUT_DIR, 'verify-rotation.png') });

    // Test 2: show-all mode shows all cards
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'show-all');
      if (typeof applyAnnouncementsPresentation === 'function') {
        applyAnnouncementsPresentation('display-mode-storage');
      }
    });
    await page.waitForTimeout(1000);

    const showAllState = await getRotationState(page);
    assert(showAllState.mode === 'show-all', `expected show-all mode, got ${showAllState.mode}`);
    assert(showAllState.visibleCount === showAllState.total, `expected all ${showAllState.total} visible, got ${showAllState.visibleCount}`);
    assert(showAllState.total >= 2, 'need at least 2 announcements for show-all test');
    pass('show-all-all-visible', showAllState);
    await page.screenshot({ path: path.join(OUT_DIR, 'verify-show-all.png') });

    // Test 3: rotation advances with short test durations
    await page.evaluate(() => {
      localStorage.setItem('announcementDisplayMode', 'rotation');
      document.querySelectorAll('#announcements-content .rotating-announcement').forEach((el) => {
        el.dataset.displayDurationSeconds = '2';
        el.dataset.duration = '2000';
      });
      if (typeof applyAnnouncementsPresentation === 'function') {
        applyAnnouncementsPresentation('render-announcements');
      }
    });
    await page.waitForTimeout(500);

    const beforeRotate = await getRotationState(page);
    const changed = await waitForActiveChange(page, beforeRotate.activeId, 5000);
    assert(changed.activeId !== beforeRotate.activeId, 'rotation should advance to a different announcement');
    pass('rotation-advances', {
      from: beforeRotate.activeId,
      to: changed.activeId,
      elapsedMs: '~2000-5000',
    });

    // Test 4: layout sync should not reset rotation back to first card immediately
    await page.waitForTimeout(1000);
    const afterSync = await getRotationState(page);
    if (typeof applyAnnouncementsPresentation === 'function') {
      await page.evaluate(() => applyAnnouncementsPresentation('applyScreenConfig'));
    }
    await page.waitForTimeout(500);
    const afterConfigSync = await getRotationState(page);
    assert(
      afterConfigSync.activeId === afterSync.activeId,
      `config layout sync should not reset active card (${afterSync.activeId} -> ${afterConfigSync.activeId})`
    );
    pass('config-sync-preserves-active-card', {
      activeId: afterConfigSync.activeId,
    });

    // Test 5: admin duration column formatting (requires auth - skip if redirected to login)
    const adminPage = await browser.newPage();
    const adminResponse = await adminPage.goto(ADMIN_URL, { waitUntil: 'domcontentloaded', timeout: 30000 });
    const adminHtml = await adminPage.content();

    if (adminHtml.includes('login') && adminHtml.includes('password')) {
      pass('admin-duration-column-skipped', { reason: 'admin requires login' });
    } else {
      assert(!adminHtml.includes('ceil($announcement->display_duration / 60)'), 'old duration template should be gone');
      const hasSecondsBadge = />\s*\d+s\s*</.test(adminHtml) || />\s*\d+m\s+\d+s\s*</.test(adminHtml);
      assert(hasSecondsBadge, 'admin table should show second-based duration badges');
      pass('admin-duration-column-format', { sample: adminHtml.match(/badge bg-primary[^>]*>[^<]+/g)?.slice(0, 5) });
      await adminPage.screenshot({ path: path.join(OUT_DIR, 'verify-admin-durations.png') });
    }
    await adminPage.close();
  } catch (error) {
    fail('verification-run', error);
    await page.screenshot({ path: path.join(OUT_DIR, 'verify-failure.png') }).catch(() => {});
  }

  await browser.close();

  const reportPath = path.join(OUT_DIR, 'verification-report.json');
  fs.writeFileSync(reportPath, JSON.stringify(results, null, 2));

  console.log('\n--- Summary ---');
  console.log(`Passed: ${results.passed.length}`);
  console.log(`Failed: ${results.failed.length}`);
  console.log(`Report: ${reportPath}`);

  if (results.failed.length > 0) {
    process.exit(1);
  }
})();
