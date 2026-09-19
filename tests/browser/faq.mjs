// Run against a local site containing CMS FAQs. No CMS data is changed.
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
    const response = await page.goto(`${base}/faqs`, { waitUntil: 'networkidle' });
    assert.equal(response.status(), 200);
    const items = page.locator('[data-faq-item]');
    const total = await items.count();
    assert.ok(total > 0, 'Populate the local CMS FAQs before running this check.');
    const first = items.first();
    const question = (await first.locator('[data-faq-question]').textContent()).trim();
    const answer = (await first.locator('[data-faq-answer]').textContent()).trim();
    const search = page.getByRole('searchbox', { name: 'Search questions and answers' });
    const states = () => items.evaluateAll(nodes => nodes.map(node => node.open));
    for (const width of [320, 375, 768, 1280, 1440]) {
        await page.setViewportSize({ width, height: 900 });
        assert.equal(await page.evaluate(() => document.documentElement.scrollWidth > innerWidth), false, `No horizontal overflow at ${width}px`);
        assert.equal(await first.locator('summary').isVisible(), true);
    }
    await first.locator('summary').focus();
    const wasOpen = await first.evaluate(node => node.open);
    await page.keyboard.press('Enter');
    assert.equal(await first.evaluate(node => node.open), !wasOpen, 'Enter toggles the native disclosure');
    await page.keyboard.press('Space');
    assert.equal(await first.evaluate(node => node.open), wasOpen, 'Space toggles the native disclosure');
    const beforeSearch = await states();
    await search.fill(question.toUpperCase());
    assert.equal(await first.isVisible(), true);
    assert.equal(await first.evaluate(node => node.open), true);
    assert.equal(await page.locator('[data-faq-no-results]').isVisible(), false);
    await search.fill(answer);
    assert.equal(await first.isVisible(), true, 'Search includes answer text');
    assert.equal(await first.locator('[data-faq-answer]').isVisible(), true);
    await page.getByRole('button', { name: 'Clear FAQ search' }).click();
    assert.equal(await items.locator('visible=true').count(), total);
    assert.deepEqual(await states(), beforeSearch, 'Clearing restores the previous open answers');
    assert.equal(await search.evaluate(node => node === document.activeElement), true);
    await search.fill('no-result-faq-qa-239847xy');
    assert.equal(await items.locator('visible=true').count(), 0);
    assert.equal(await page.locator('[data-faq-no-results]').isVisible(), true);
    assert.equal(await page.locator('[data-faq-count]').textContent(), '0 answers found');
    assert.equal(await page.locator('[data-faq-expand]').isVisible(), false);
    await page.getByRole('button', { name: 'Show all questions' }).click();
    assert.deepEqual(await states(), beforeSearch);
    const expand = page.locator('[data-faq-expand]');
    if ((await states()).every(Boolean)) await expand.click();
    await expand.click();
    assert.ok((await states()).every(Boolean), 'Expand all opens each answer');
    await expand.click();
    assert.ok((await states()).every(open => !open), 'Collapse all closes each answer');
    await first.locator('summary').click();
    assert.equal(await page.locator('.faq-contact-link').getAttribute('href'), '#contact');
    if (process.env.QA_SCREENSHOT_DIR) {
        fs.mkdirSync(process.env.QA_SCREENSHOT_DIR, { recursive: true });
        for (const width of [375, 1440]) {
            await page.setViewportSize({ width, height: 1000 });
            await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
            await page.screenshot({ path: `${process.env.QA_SCREENSHOT_DIR}/faq-${width}.png`, fullPage: true });
        }
    }
    // Native answers remain available if scripts are blocked or disabled.
    const noJs = await browser.newContext({ javaScriptEnabled: false, viewport: { width: 375, height: 900 } });
    const plain = await noJs.newPage();
    await plain.goto(`${base}/faqs`);
    const plainFirst = plain.locator('[data-faq-item]').first();
    const originallyOpen = await plainFirst.getAttribute('open');
    await plainFirst.locator('summary').focus();
    await plain.keyboard.press('Enter');
    assert.equal(await plainFirst.getAttribute('open'), originallyOpen === null ? '' : null);
    assert.equal(await plain.locator('[data-faq-search-tools]').isVisible(), false);
    await noJs.close();
    assert.deepEqual(errors, []);
    console.log('PASS: FAQ layouts at 320–1440px; question/answer search; empty results; clear and restore; expand/collapse; keyboard controls; answers usable without JavaScript.');
} finally {
    await browser.close();
}
