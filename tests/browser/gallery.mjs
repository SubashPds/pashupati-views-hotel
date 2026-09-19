// Run against a local site with gallery content. No CMS data is changed.
import assert from 'node:assert/strict';
import fs from 'node:fs';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:9000';
const browser = await chromium.launch({ executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });
try {
    const page = await browser.newPage();
    await page.addInitScript(() => sessionStorage.setItem('hotel.promotions.seen', '1'));
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const response = await page.goto(`${base}/gallery`, { waitUntil: 'networkidle' });
    assert.equal(response.status(), 200);
    // Dismiss the existing site-wide offers dialog if it is displayed.
    const offer = page.locator('dialog[open]').filter({ hasNot: page.locator('[data-gallery-image]') });
    if (await offer.count()) await page.keyboard.press('Escape');
    const cards = page.locator('[data-gallery-category]');
    const total = await cards.count();
    assert.ok(total > 0, 'Populate the local CMS gallery before this browser check.');
    for (const width of [375, 768, 1280, 1440]) {
        await page.setViewportSize({ width, height: 900 });
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `No horizontal overflow at ${width}px`);
        assert.equal(await page.locator('.gallery-card-caption h3').first().isVisible(), true);
    }
    const filters = page.locator('[data-gallery-filter]');
    for (const filter of await filters.all()) {
        const category = await filter.getAttribute('data-gallery-filter');
        await filter.click();
        const visible = cards.locator('visible=true');
        const categories = await visible.evaluateAll(items => items.map(item => item.dataset.galleryCategory));
        assert.ok(categories.length > 0);
        if (category) assert.ok(categories.every(value => value === category));
        assert.equal(await filter.getAttribute('aria-pressed'), 'true');
        assert.equal(await page.locator('[data-gallery-featured]:visible').count(), 1);
        const buttons = page.locator('[data-gallery-open]:visible');
        const count = await buttons.count();
        if (count) {
            await buttons.first().click();
            const viewer = page.locator('#gallery-viewer');
            assert.equal(await viewer.evaluate(dialog => dialog.open), true);
            assert.match(await page.locator('[data-gallery-position]').textContent(), new RegExp(`1 / ${count}$`));
            if (count > 1) {
                await page.keyboard.press('ArrowRight');
                assert.match(await page.locator('[data-gallery-position]').textContent(), new RegExp(`2 / ${count}$`));
                await page.keyboard.press('ArrowLeft');
                assert.match(await page.locator('[data-gallery-position]').textContent(), new RegExp(`1 / ${count}$`));
            } else {
                assert.equal(await page.locator('[data-gallery-next]').isVisible(), false);
            }
            await page.keyboard.press('Escape');
            assert.equal(await viewer.evaluate(dialog => dialog.open), false);
            assert.equal(await buttons.first().evaluate(el => el === document.activeElement), true);
            await page.waitForFunction(() => document.body.style.overflow !== 'hidden');
        }
    }
    await filters.first().click();
    assert.equal(await cards.locator('visible=true').count(), total);
    // Simulate a failed preview and ensure the fallback remains readable.
    const preview = page.locator('img[data-gallery-preview]').first();
    if (await preview.count()) {
        await preview.dispatchEvent('error');
        assert.equal(await preview.isVisible(), false);
        assert.equal(await preview.locator('..').locator('[data-gallery-fallback]').isVisible(), true);
    }
    if (process.env.QA_SCREENSHOT_DIR) {
        fs.mkdirSync(process.env.QA_SCREENSHOT_DIR, { recursive: true });
        for (const width of [375, 1440]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
            await page.screenshot({ path: `${process.env.QA_SCREENSHOT_DIR}/gallery-${width}.png`, fullPage: true });
        }
    }
    // The homepage continues to use the shared viewer with its existing gallery.
    await page.goto(`${base}/`, { waitUntil: 'networkidle' });
    if (await page.locator('dialog[open]').count()) await page.keyboard.press('Escape');
    await page.locator('[data-gallery-open]').first().click();
    assert.equal(await page.locator('#gallery-viewer').evaluate(dialog => dialog.open), true);
    await page.keyboard.press('Escape');
    assert.deepEqual(errors, []);
    console.log('PASS: responsive gallery, all category filters, filtered viewer navigation, Escape/focus restoration, missing-image fallback, and homepage viewer.');
} finally {
    await browser.close();
}
