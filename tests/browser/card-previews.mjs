// Run after npm run build. Real Blade fixtures, with no database or external requests.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFile } from 'node:fs/promises';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const fixture = JSON.parse(execFileSync(process.env.QA_PHP || 'php', ['tests/browser/fixtures/card-previews.php'], { encoding: 'utf8' }));
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
            if (path === '/') return route.fulfill({ contentType: 'text/html', body: fixture.html });
            if (path === '/qa/app.css') return route.fulfill({ contentType: 'text/css', body: css });
            if (path.startsWith('/resources/js/')) return route.fulfill({ contentType: 'text/javascript', body: await readFile(`.${path}${path.endsWith('.js') ? '' : '.js'}`, 'utf8') });
            requests.push(path);
            if (path.endsWith('/details')) return route.fulfill({ contentType: 'text/html', body: path.startsWith('/rooms') ? fixture.roomDetails : fixture.packageDetails });
            pending++;
            maxPending = Math.max(maxPending, pending);
            await new Promise(resolve => setTimeout(resolve, 100));
            pending--;
            if (path === '/qa/previews/room-3.webp') return route.fulfill({ status: 404, body: '' });
            return route.fulfill({ contentType: 'image/svg+xml', body: '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="600"><rect width="800" height="600" fill="tan"/></svg>' });
        });
        await page.goto('http://cards-qa.test/', { waitUntil: 'networkidle' });
        assert.deepEqual(requests, [], 'Offscreen cards must not request images or details');
        assert.equal(await page.locator('.room-card[data-scroll-reveal], .package-card[data-scroll-reveal]').count(), 0);

        // Filtering an offscreen room must keep its thumbnail deferred.
        await page.locator('[data-filter="premium"]').evaluate(el => el.click());
        await page.locator('.room-card[data-category="premium"]').scrollIntoViewIfNeeded();
        await page.waitForFunction(() => !document.querySelector('.room-card[data-category="premium"] [data-card-preview-src]'));
        assert.ok(!requests.includes('/qa/previews/room-1.webp'));
        assert.ok(!requests.includes('/qa/previews/room-3.webp'));
        await page.locator('[data-filter="all"]').click();
        const heightsBefore = await page.locator('.room-card, .package-card').evaluateAll(cards => cards.map(el => el.offsetHeight));
        for (const card of await page.locator('.room-card, .package-card').all()) {
            await card.scrollIntoViewIfNeeded();
            await page.waitForFunction(el => !el.querySelector('[data-card-preview-src]'), await card.elementHandle());
        }
        assert.ok(maxPending <= 2, 'Both card types share a limit of two downloads');
        assert.equal(requests.length, 6, 'Every thumbnail is requested exactly once');
        assert.ok(requests.every(path => path.startsWith('/qa/previews/')), 'Scrolling must not request original images');
        assert.deepEqual(await page.locator('.room-card, .package-card').evaluateAll(cards => cards.map(el => el.offsetHeight)), heightsBefore);
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false);
        const failed = page.locator('img[src="/qa/previews/room-3.webp"]');
        assert.equal(await failed.isVisible(), false);
        assert.equal(await failed.locator('..').locator('[data-card-preview-fallback]').isVisible(), true);
        await page.evaluate(() => scrollTo({ top: 0, behavior: 'instant' }));
        await page.locator('#rooms').scrollIntoViewIfNeeded();
        assert.equal(requests.length, 6);

        for (const kind of ['room', 'package']) {
            await page.locator(`[data-${kind}-details]`).first().click();
            const dialog = page.locator(`.${kind}-details-dialog[open]`);
            await dialog.waitFor();
            assert.ok((await dialog.locator(`[data-${kind}-photo]`).getAttribute('src')).endsWith(`/qa/originals/${kind}-1.jpg`));
            await page.keyboard.press('Escape');
            await dialog.waitFor({ state: 'detached' });
        }
        assert.deepEqual(errors, []);
        console.log(`PASS ${width}px: no offscreen/original requests, shared loading limit, room filters, stable cards, fallback, no repeated loads, original images in on-demand details.`);
        await page.close();
    }
} finally {
    await browser.close();
}
