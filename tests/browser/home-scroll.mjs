// Run against a local site with rooms, packages, and a hero video. No CMS data is changed.
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:9000';
const browser = await chromium.launch({ executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });

try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 900 } });
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => {
        if (location.protocol === 'http:' || location.protocol === 'https:') sessionStorage.setItem('hotel.promotions.seen', '1');
    });
    const response = await page.goto(base, { waitUntil: 'domcontentloaded' });
    assert.equal(response.status(), 200);
    await page.evaluate(() => document.fonts.ready);

    async function scrollToSection(id) {
        await page.evaluate(id => {
            const section = document.getElementById(id === 'home' ? 'hero-section' : id);
            const height = document.querySelector('[data-navigation-bar]').getBoundingClientRect().height;
            window.scrollTo({ top: id === 'home' ? 0 : section.getBoundingClientRect().top + scrollY - height - 12, behavior: 'instant' });
        }, id);
        await page.waitForFunction(id => {
            const links = [...document.querySelectorAll('#site-header .nav-link[aria-current="location"]')];
            return links.length === 2 && links.every(link => link.getAttribute('href') === `#${id}`);
        }, id);
    }

    const hasVideo = await page.locator('[data-slide]:not([hidden]) video').count();
    if (hasVideo) await page.waitForFunction(() => [...document.querySelectorAll('[data-slide] video')].some(video => !video.paused));
    for (const width of [1440, 390]) {
        await page.setViewportSize({ width, height: 900 });
        await page.waitForTimeout(300);
        for (const section of ['rooms', 'packages', 'experience', 'services', 'home']) {
            if (section === 'home' || await page.locator(`#${section}`).count()) await scrollToSection(section);
        }
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `No horizontal overflow at ${width}px`);
    }

    // Room filtering changes the height above packages; cached offsets must refresh.
    const filter = page.locator('.room-filter-btn:not([data-filter="all"])').first();
    if (await filter.count()) {
        await scrollToSection('rooms');
        await filter.click();
        await page.waitForTimeout(300);
        await scrollToSection('packages');
        await page.locator('.room-filter-btn[data-filter="all"]').click();
        await page.waitForTimeout(300);
    }

    await scrollToSection('packages');
    await page.waitForFunction(() => [...document.querySelectorAll('[data-slide] video')].every(video => video.paused));
    await page.waitForTimeout(500);
    const work = await page.evaluate(async () => {
        const sectionIds = new Set(['hero-section', 'rooms', 'packages', 'experience', 'services']);
        const originals = new Map();
        let layoutReads = 0;
        let navigationWrites = 0;
        for (const method of ['getBoundingClientRect', 'getClientRects']) {
            const original = Element.prototype[method];
            originals.set(method, original);
            Element.prototype[method] = function (...args) {
                if (sectionIds.has(this.id) || this.hasAttribute('data-navigation-bar')) layoutReads++;
                return original.apply(this, args);
            };
        }
        const observer = new MutationObserver(records => { navigationWrites += records.length; });
        observer.observe(document.getElementById('site-header'), { subtree: true, attributes: true, attributeFilter: ['aria-current', 'style'] });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['style'] });
        try {
            const top = scrollY;
            for (let step = 1; step <= 30; step++) {
                window.scrollTo({ top: top + step * 3, behavior: 'instant' });
                await new Promise(requestAnimationFrame);
            }
            return { layoutReads, navigationWrites };
        } finally {
            observer.disconnect();
            originals.forEach((original, method) => { Element.prototype[method] = original; });
        }
    });
    assert.equal(work.layoutReads, 0, 'Steady scrolling must not remeasure navigation sections');
    assert.equal(work.navigationWrites, 0, 'Staying in one section must not rewrite navigation or header styles');

    // Hero playback resumes on return, and honors the visitor's motion preference.
    await scrollToSection('home');
    if (hasVideo) {
        await page.waitForFunction(() => [...document.querySelectorAll('[data-slide] video')].some(video => !video.paused));
        await page.emulateMedia({ reducedMotion: 'reduce' });
        await page.waitForFunction(() => [...document.querySelectorAll('[data-slide] video')].every(video => video.paused));
        await page.emulateMedia({ reducedMotion: 'no-preference' });
    }

    // Loading a section directly must also initialize the current navigation link.
    await page.goto('about:blank');
    await page.goto(`${base}/#packages`, { waitUntil: 'domcontentloaded' });
    await page.waitForFunction(() => document.querySelector('#site-header a[href="#packages"][aria-current="location"]'));
    await page.locator('[data-package-details]').first().click();
    assert.equal(await page.locator('.package-details-dialog[open]').count(), 1);
    await page.keyboard.press('Escape');
    await page.locator('[data-room-details]').first().click();
    assert.equal(await page.locator('.room-details-dialog[open]').count(), 1);
    await page.keyboard.press('Escape');
    assert.deepEqual(errors, []);
    console.log('PASS: desktop/mobile navigation, room filters, direct section links, dialogs, no repeated scroll layout/style work, and offscreen/reduced-motion hero playback.');
} finally {
    await browser.close();
}
