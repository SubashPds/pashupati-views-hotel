// Enquiry requests are intercepted: this check creates no enquiries and sends no email.
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const base = process.env.QA_BASE_URL || 'http://127.0.0.1:9000';
const browser = await chromium.launch({ executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });

try {
    const page = await browser.newPage({ viewport: { width: 1440, height: 1000 } });
    const errors = [];
    const requests = [];
    let reply;
    let gate;
    page.on('pageerror', error => errors.push(error.message));
    await page.addInitScript(() => sessionStorage.setItem('hotel.promotions.seen', '1'));
    await page.route('**/enquire', async route => {
        assert.equal(route.request().method(), 'POST');
        assert.equal(route.request().headers().accept, 'application/json');
        assert.ok(route.request().postData().includes('_token'), 'Send the CSRF form token');
        requests.push(route.request().postData());
        const result = reply;
        if (gate) await gate;
        if (result.abort) return route.abort('failed');
        await route.fulfill({ status: result.status, headers: result.headers, contentType: result.html ? 'text/html' : 'application/json', body: result.html || JSON.stringify(result.body) });
    });
    await page.goto(base, { waitUntil: 'domcontentloaded' });
    await page.waitForFunction(() => document.getElementById('booking-form').noValidate);
    await page.evaluate(() => { window.ajaxDocumentMarker = 'same document'; });
    let navigations = 0;
    page.on('framenavigated', frame => { if (frame === page.mainFrame()) navigations++; });
    const contact = page.locator('#contact [data-ajax-form="enquiry"]');
    const submit = contact.locator('button[type="submit"]');
    const feedback = contact.locator('[data-form-feedback]');

    reply = { status: 422, body: { errors: { guest_name: ['Please enter your name.'], contact: ['Please provide email or phone.'] } } };
    await submit.click();
    await page.waitForFunction(() => document.getElementById('c-name').getAttribute('aria-invalid') === 'true');
    assert.equal(await page.locator('#c-email').getAttribute('aria-invalid'), 'true');
    assert.equal(await page.locator('#c-phone').getAttribute('aria-invalid'), 'true');
    assert.equal(await page.locator('#c-name').evaluate(field => field === document.activeElement), true);
    assert.ok((await feedback.innerText()).includes('Please provide email or phone.'));

    await page.locator('#c-name').fill('AJAX Browser Guest');
    await page.locator('#c-email').fill('guest@example.test');
    await page.locator('#c-msg').fill('Keep this message while sending.');
    reply = { status: 201, body: { message: 'Thank you! Your enquiry was received.' } };
    let release;
    gate = new Promise(resolve => { release = resolve; });
    const before = requests.length;
    await contact.evaluate(form => { form.requestSubmit(); form.requestSubmit(); });
    await page.waitForFunction(() => document.querySelector('#contact form').getAttribute('aria-busy') === 'true');
    assert.equal(await submit.isDisabled(), true);
    assert.equal(await page.locator('#c-msg').inputValue(), 'Keep this message while sending.');
    await page.waitForTimeout(150);
    assert.equal(requests.length, before + 1, 'Repeated submit events create only one request');
    release();
    gate = null;
    await page.waitForFunction(() => document.querySelector('#contact [data-form-feedback]').dataset.state === 'success');
    assert.equal(await page.locator('#c-name').inputValue(), '');
    assert.equal(await page.locator('#c-name').getAttribute('aria-invalid'), null);
    assert.equal(await submit.isEnabled(), true);
    assert.ok((await submit.innerText()).includes('Send Message'));

    for (const failure of [
        { status: 419, html: '<html>Page Expired</html>' },
        { status: 500, html: '<html>Server Error</html>' },
        { abort: true },
    ]) {
        reply = failure;
        await page.locator('#c-name').fill('Keep my name');
        await page.locator('#c-msg').fill('Keep my message');
        await submit.click();
        await page.waitForFunction(() => document.querySelector('#contact [data-form-feedback]').dataset.state === 'error' && !document.querySelector('#contact form').hasAttribute('aria-busy'));
        assert.equal(await feedback.isVisible(), true);
        assert.equal(await page.locator('#c-name').inputValue(), 'Keep my name');
        assert.equal(await page.locator('#c-msg').inputValue(), 'Keep my message');
        assert.equal(await submit.isEnabled(), true);
    }

    // A server throttle preserves input and prevents local retries across both enquiry forms.
    reply = { status: 429, headers: { 'Retry-After': '2' }, body: { message: 'Too many requests.', retry_after: 2 } };
    await submit.click();
    await page.waitForFunction(() => document.querySelector('#contact [data-form-feedback]').textContent.includes('Please wait 2 seconds'));
    assert.equal(await page.locator('#c-msg').inputValue(), 'Keep my message');
    const throttledCount = requests.length;
    await contact.evaluate(form => form.requestSubmit());
    await page.evaluate(() => { window.openBooking(); document.getElementById('booking-form').requestSubmit(); });
    assert.equal(requests.length, throttledCount, 'Contact and booking share the retry cooldown');
    assert.ok((await page.locator('#booking-form [data-form-feedback]').innerText()).includes('Please wait'));
    await page.keyboard.press('Escape');
    await page.waitForTimeout(2100);

    // Booking feedback stays inside the modal, where it is readable and announced.
    await page.evaluate(() => window.openBooking());
    const booking = page.locator('#booking-form');
    await booking.locator('[name="guest_name"]').fill('Booking Guest');
    await booking.locator('[name="phone"]').fill('9800000000');
    reply = { status: 422, body: { errors: { checkout: ['Check-out must follow check-in.'] } } };
    await booking.locator('button[type="submit"]').click();
    await page.waitForFunction(() => document.getElementById('b-checkout').getAttribute('aria-invalid') === 'true');
    assert.equal(await booking.locator('[name="guest_name"]').inputValue(), 'Booking Guest');
    reply = { status: 201, body: { message: 'Thank you! Your enquiry was received.' } };
    await booking.locator('button[type="submit"]').click();
    await page.waitForFunction(() => document.querySelector('#booking-form [data-form-feedback]').dataset.state === 'success');
    assert.equal(await booking.locator('[data-form-feedback]').isVisible(), true);
    assert.equal(await page.locator('#booking-dialog').evaluate(dialog => dialog.open), true);
    assert.equal(await page.locator('#c-msg').inputValue(), 'Keep my message', 'Booking success must not reset contact inputs');
    await page.keyboard.press('Escape');

    // These real requests change only the browser's preference cookie.
    const currency = page.locator('#display-currency');
    assert.equal(await page.locator('[data-country-prompt]').count(), 1);
    const countryResponse = page.waitForResponse(response => response.url().endsWith('/currency') && response.request().method() === 'POST');
    await page.locator('[data-country-prompt] button[value="IN"]').click();
    const countryData = await (await countryResponse).json();
    assert.equal(countryData.currency, 'INR');
    await page.waitForFunction(() => !document.querySelector('[data-country-prompt]'));
    assert.equal(await currency.inputValue(), 'INR');
    const priceResponse = page.waitForResponse(response => response.url().endsWith('/currency') && response.request().method() === 'POST');
    await currency.selectOption('USD');
    const prices = await (await priceResponse).json();
    await page.waitForFunction(() => !document.querySelector('#display-currency').disabled);
    for (const element of await page.locator('[data-currency-price]').all()) {
        assert.equal(await element.textContent(), prices.prices[await element.getAttribute('data-currency-price')]);
    }
    for (const element of await page.locator('[data-currency-code]').all()) assert.equal(await element.textContent(), 'USD');
    await page.route('**/currency', route => route.fulfill({ status: 500, contentType: 'text/html', body: 'Unavailable' }));
    await currency.selectOption('NPR');
    await page.waitForFunction(() => document.querySelector('#site-header [data-form-feedback]').dataset.state === 'error');
    assert.equal(await currency.inputValue(), 'USD', 'A failed preference change keeps the selector consistent with prices');
    assert.equal(await page.evaluate(() => window.ajaxDocumentMarker), 'same document');
    assert.equal(navigations, 0, 'Submitting frontend forms never navigates or refreshes the document');

    await page.setViewportSize({ width: 390, height: 844 });
    assert.equal(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), true);
    // The global contact form on dedicated frontend pages uses the same handler.
    await page.goto(`${base}/contact`, { waitUntil: 'domcontentloaded' });
    await page.waitForFunction(() => document.getElementById('booking-form').noValidate);
    await page.locator('#c-name').fill('Contact Page Guest');
    await page.locator('#c-phone').fill('9800000000');
    reply = { status: 201, body: { message: 'Thank you! Your enquiry was received.' } };
    const navigationCount = navigations;
    await page.locator('#contact button[type="submit"]').click();
    await page.waitForFunction(() => document.querySelector('#contact [data-form-feedback]').dataset.state === 'success');
    assert.equal(navigations, navigationCount);
    assert.deepEqual(errors, []);
    console.log('PASS: AJAX contact/booking success, validation, duplicate protection, retained inputs on errors, shared 429 retry cooldown and recovery, currency updates, mobile layout, and no page refresh.');
} finally {
    await browser.close();
}
