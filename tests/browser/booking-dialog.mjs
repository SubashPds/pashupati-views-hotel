// Run after npm run build. All requests are fulfilled locally; no enquiries or emails are sent.
import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { readFile } from 'node:fs/promises';

const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const html = execFileSync(process.env.QA_PHP || 'php', ['tests/browser/fixtures/booking-dialog.php'], { encoding: 'utf8' });
const manifest = JSON.parse(await readFile('public/build/manifest.json', 'utf8'));
const css = await readFile(`public/build/${manifest['resources/css/app.css'].file}`, 'utf8');
const browser = await chromium.launch({
    executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome',
    headless: true,
    args: ['--no-sandbox'],
});

try {
    for (const width of [390, 1440]) {
        const page = await browser.newPage({ viewport: { width, height: 1000 } });
        const errors = [];
        const requests = [];
        let reply;
        let gate;
        page.on('pageerror', error => errors.push(error.message));
        await page.route('**/*', async route => {
            const request = route.request();
            const url = new URL(request.url());
            if (url.origin !== 'http://booking.test') return route.abort();
            if (url.pathname === '/') return route.fulfill({ contentType: 'text/html', body: html });
            if (url.pathname === '/app.css') return route.fulfill({ contentType: 'text/css', body: css });
            if (['/resources/js/secure-actions.js', '/resources/js/frontend-forms.js'].includes(url.pathname)) {
                return route.fulfill({ contentType: 'text/javascript', body: await readFile(`.${url.pathname}`, 'utf8') });
            }
            if (url.pathname === '/enquire') {
                assert.equal(request.method(), 'POST');
                assert.equal(request.headers().accept, 'application/json');
                requests.push(request.postData());
                const result = reply;
                await gate;
                return route.fulfill({ status: result.status, contentType: 'application/json', body: JSON.stringify(result.body) });
            }
            return route.abort();
        });
        await page.goto('http://booking.test/', { waitUntil: 'networkidle' });
        await page.waitForFunction(() => document.getElementById('booking-form').noValidate);
        const dialog = page.locator('#booking-dialog');
        const submit = page.locator('#booking-form button[type="submit"]');
        const feedback = page.locator('#booking-form [data-form-feedback]');
        const successToast = page.locator('#enquiry-success-toast');

        for (const activation of ['Enter', 'Space', 'implicit Enter', 'pointer']) {
            for (const status of [422, 201]) {
                await page.locator('[data-open-booking]').click();
                await page.locator('#b-name').fill('Keyboard Guest');
                reply = status === 422
                    ? { status, body: { errors: { contact: ['Please provide email or phone.'] } } }
                    : { status, body: { message: 'Your enquiry was received.' } };
                let release;
                gate = new Promise(resolve => { release = resolve; });
                const before = requests.length;
                try {
                    const request = page.waitForRequest('**/enquire');
                    if (activation === 'pointer') {
                        await submit.click();
                    } else {
                        await (activation === 'implicit Enter' ? page.locator('#b-name') : submit).focus();
                        await page.keyboard.press(activation === 'implicit Enter' ? 'Enter' : activation);
                    }
                    await request;
                    assert.equal(await dialog.evaluate(element => element.open), true, `${activation}: dialog stays open while sending`);
                    assert.equal(await submit.isDisabled(), true);
                } finally {
                    release();
                }
                await page.waitForFunction(() => !document.getElementById('booking-form').hasAttribute('aria-busy'));
                assert.equal(requests.length, before + 1, `${activation}: exactly one request`);
                assert.equal(await page.locator('#b-name').inputValue(), status === 422 ? 'Keyboard Guest' : '');
                assert.equal(await submit.isEnabled(), true);
                if (status === 422) {
                    assert.equal(await dialog.evaluate(element => element.open), true, `${activation}: validation keeps the dialog open`);
                    assert.equal(await feedback.isVisible(), true, `${activation}: validation feedback is visible`);
                    assert.equal(await feedback.getAttribute('data-state'), 'error');
                    assert.equal(await page.locator('#b-phone').getAttribute('aria-invalid'), 'true');
                    assert.equal(await page.locator('#b-phone').evaluate(element => element === document.activeElement), true);
                    assert.equal(await page.evaluate(() => document.body.style.overflow), 'hidden');
                    await page.keyboard.press('Escape');
                } else {
                    assert.equal(await dialog.evaluate(element => element.open), false, `${activation}: success closes the dialog`);
                    assert.equal(await successToast.isVisible(), true, `${activation}: success message is visible outside the dialog`);
                    assert.equal(await successToast.innerText(), reply.body.message);
                    assert.equal(await successToast.evaluate(element => element === document.activeElement), true);
                    const toastRect = await successToast.boundingBox();
                    assert.ok(toastRect.x >= 0 && toastRect.x + toastRect.width <= width, 'Success message fits the viewport');
                }
                await page.waitForFunction(() => !document.getElementById('booking-dialog').open && document.body.style.overflow === '');
            }
        }

        await page.locator('[data-open-booking]').click();
        await page.locator('#b-name').fill('Unsent Guest');
        await page.locator('#b-name').click();
        assert.equal(await dialog.evaluate(element => element.open), true, 'A click inside stays open');
        const rect = await dialog.boundingBox();
        assert.ok(rect.x > 1 || rect.y > 1, 'The test has a backdrop outside the dialog');
        await page.mouse.click(1, 1);
        assert.equal(await dialog.evaluate(element => element.open), true, 'An outside click keeps the dialog open');
        assert.equal(await page.locator('#b-name').inputValue(), 'Unsent Guest', 'An outside click preserves entered values');
        assert.equal(await page.evaluate(() => document.body.style.overflow), 'hidden');
        await page.locator('[data-close-booking]').click();
        await page.waitForFunction(() => !document.getElementById('booking-dialog').open && document.body.style.overflow === '');
        assert.deepEqual(errors, []);
        console.log(`PASS ${width}px: Enter, Space, implicit Enter, pointer submit, validation keeps the form open, success closes it and shows a focused confirmation, outside clicks preserve the form, close/Escape dismissal, and scroll restoration.`);
        await page.close();
    }
} finally {
    await browser.close();
}
