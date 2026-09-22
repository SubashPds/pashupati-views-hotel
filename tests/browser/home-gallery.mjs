// Isolated real-Blade fixture: no database, web server, or CMS writes required.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFile } from 'node:fs/promises';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const html = execFileSync(process.env.QA_PHP || 'php', ['tests/browser/fixtures/home-gallery.php'], { encoding: 'utf8' });
const manifest = JSON.parse(await readFile('public/build/manifest.json', 'utf8'));
const css = await readFile(`public/build/${manifest['resources/css/app.css'].file}`, 'utf8');
const browser = await chromium.launch({ executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });

try {
    for (const width of [390, 1440]) {
        const page = await browser.newPage({ viewport: { width, height: 900 } });
        const requests = [];
        const errors = [];
        let pending = 0;
        let maxPending = 0;
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const path = new URL(route.request().url()).pathname;
            if (path === '/') return route.fulfill({ contentType: 'text/html', body: html });
            if (path === '/qa/app.css') return route.fulfill({ contentType: 'text/css', body: css });
            if (path.startsWith('/resources/js/')) return route.fulfill({ contentType: 'text/javascript', body: await readFile(`.${path}`, 'utf8') });
            requests.push(path);
            pending++;
            maxPending = Math.max(maxPending, pending);
            await new Promise(resolve => setTimeout(resolve, 100));
            pending--;
            if (path === '/qa/photo-3.jpg' || path.endsWith('.mp4')) return route.fulfill({ status: 404, body: '' });
            return route.fulfill({ contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="tan"/></svg>' });
        });
        await page.goto('http://gallery-qa.test/', { waitUntil: 'networkidle' });
        assert.deepEqual(requests, [], 'Offscreen gallery must not fetch images or video');
        assert.equal(await page.locator('#gallery video').count(), 0);
        assert.equal(await page.locator('#gallery .gallery-cell[data-scroll-reveal]').count(), 0);
        assert.equal(await page.locator('.gallery-cell').count(), 8);
        assert.equal(await page.getByText('View Full Gallery', { exact: true }).count(), 1);
        const heightBefore = await page.locator('#gallery').evaluate(el => el.offsetHeight);

        await page.locator('#gallery').scrollIntoViewIfNeeded();
        for (const tile of await page.locator('.gallery-cell').all()) {
            await tile.scrollIntoViewIfNeeded();
            await page.waitForFunction(el => !el.querySelector('[data-home-gallery-src]'), await tile.elementHandle());
        }
        await page.waitForFunction(() => !document.querySelector('[data-home-gallery-src]'));
        assert.ok(maxPending <= 2, 'Preview downloads must be limited to two at a time');
        assert.equal(requests.filter(path => path.endsWith('.mp4')).length, 0, 'Scrolling must not load videos');
        assert.equal(requests.length, 7, 'Each photo preview loads once');
        assert.equal(await page.locator('#gallery').evaluate(el => el.offsetHeight), heightBefore, 'Loading previews must not shift gallery height');
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
        const failed = page.locator('[data-gallery-preview][src$="/qa/photo-3.jpg"]');
        assert.equal(await failed.isVisible(), false);
        assert.equal(await failed.locator('..').locator('[data-gallery-fallback]').isVisible(), true);

        const beforeReturn = requests.length;
        await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
        await page.locator('#gallery').scrollIntoViewIfNeeded();
        assert.equal(requests.length, beforeReturn, 'Returning to the gallery must not reload previews');
        const open = page.locator('[data-gallery-open]').first();
        await open.click();
        assert.equal(await page.locator('#gallery-viewer').evaluate(el => el.open), true);
        assert.equal(new URL(await page.locator('[data-gallery-image]').getAttribute('src')).pathname, '/qa/photo-1.jpg');
        await page.keyboard.press('ArrowRight');
        await page.waitForFunction(() => document.querySelector('[data-gallery-video]').getAttribute('src')?.endsWith('/qa/video.mp4'));
        await page.waitForTimeout(200);
        assert.ok(requests.includes('/qa/video.mp4'), 'Video loads only when selected in viewer');
        await page.keyboard.press('Escape');
        await page.waitForFunction(() => !document.querySelector('[data-gallery-video]').hasAttribute('src'));
        assert.equal(await page.evaluate(() => document.body.style.overflow), '');
        assert.equal(await open.evaluate(el => el === document.activeElement), true);
        assert.deepEqual(errors, []);
        console.log(`PASS ${width}px: deferred and bounded loading, no video preload, stable layout, fallback, no repeat loading, full viewer and cleanup.`);
        await page.close();
    }
} finally {
    await browser.close();
}
