// Run only against the isolated dashboard QA fixture (array mailer, SQLite).
import assert from 'node:assert/strict';
const { chromium } = await import('/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const browser = await chromium.launch({ executablePath: '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });
const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
const go = path => page.goto('http://127.0.0.1:9011' + path, { waitUntil: 'domcontentloaded' });
try {
    await go('/login');
    await page.locator('#email').fill('qa@example.test');
    await page.locator('#password').fill('Qa-Only-2026!');
    await page.locator('button[type=submit]').click();
    await page.waitForURL('**/admin/dashboard');
    await go('/admin/settings');
    await page.locator('[data-tab=contact]').click();
    const emails = page.locator('#setting_contact_email');
    assert.equal(await emails.evaluate(el => el.tagName), 'TEXTAREA');
    await emails.fill('RECEPTION@example.test\nmanager@example.test, reception@example.test');
    await page.getByRole('button', { name: 'Save All Settings' }).click();
    await page.waitForLoadState('domcontentloaded');
    assert.equal(await emails.inputValue(), 'reception@example.test, manager@example.test');
    console.log('PASS dashboard saves and deduplicates multiple recipients');

    await go('/');
    assert.equal(await page.locator('a[href="mailto:reception@example.test"]').count(), 2);
    assert.equal(await page.locator('a[href="mailto:manager@example.test"]').count(), 2);
    await page.locator('#c-name').fill('QA Email Contact');
    await page.locator('#c-email').fill('guest@example.test');
    await page.locator('#c-msg').fill('Browser contact notification check');
    await page.getByRole('button', { name: 'Send Message' }).click();
    await page.locator('#contact [data-form-feedback][data-state="success"]').waitFor({ state: 'visible' });
    console.log('PASS contact submission succeeds with multiple recipients');

    await go('/');
    await page.locator('[onclick*="openBooking"]').first().click();
    await page.locator('#b-name').fill('QA Email Booking');
    await page.locator('#b-phone').fill('9800000000');
    await page.locator('#b-checkin').fill('2026-10-01');
    await page.locator('#b-checkout').fill('2026-10-03');
    await page.locator('#b-guests').selectOption('3');
    await page.locator('#booking-form button[type=submit]').click();
    await page.locator('#booking-form [data-form-feedback][data-state="success"]').waitFor({ state: 'visible' });
    await go('/admin/enquiries');
    await page.getByRole('link', { name: 'QA Email Booking', exact: true }).click();
    for (const detail of ['Check-in: 2026-10-01', 'Check-out: 2026-10-03', 'Guests: 3']) {
        assert.ok((await page.locator('body').innerText()).includes(detail));
    }
    console.log('PASS phone-only booking retains stay details in dashboard');

    await page.setViewportSize({ width: 375, height: 812 });
    await go('/');
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
    console.log('PASS multiple contact emails fit mobile viewport');
} finally {
    await browser.close();
}
