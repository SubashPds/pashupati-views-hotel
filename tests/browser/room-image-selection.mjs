// Run with Node and QA_PLAYWRIGHT_MODULE pointing to an installed Playwright module.
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import assert from 'node:assert/strict';
const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const temp = fs.mkdtempSync(path.join(os.tmpdir(), 'room-image-selection-'));
const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=', 'base64');
const files = ['one.png', 'two.png', 'three.png'].map(name => {
    const file = path.join(temp, name);
    fs.writeFileSync(file, png);
    return file;
});
const browser = await chromium.launch({ executablePath: process.env.QA_CHROME_PATH || '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });
try {
    const page = await browser.newPage();
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    const block = (name, multiple) => `<div data-room-image-upload id="${name}">
        ${!multiple ? '<img data-saved-cover alt="Saved cover">' : ''}
        <input type="file" name="${multiple ? 'gallery_images[]' : 'cover_image'}" ${multiple ? 'multiple' : ''}>
        <div data-upload-preview hidden><div data-preview-images></div><button type="button" data-clear-upload>Clear selection</button></div>
        <p data-upload-status role="status"></p>
    </div>`;
    await page.setContent(`<form>${block('cover', false)}${block('gallery', true)}<button type="reset">Reset</button></form>`);
    await page.addScriptTag({ content: fs.readFileSync(new URL('../../resources/js/room-image-preview.js', import.meta.url), 'utf8') });
    const gallery = page.locator('#gallery input');
    const cover = page.locator('#cover input');
    const submitted = () => page.locator('form').evaluate(form => new FormData(form).getAll('gallery_images[]').filter(file => file.size).map(file => file.name));
    await gallery.setInputFiles(files.slice(0, 2));
    await gallery.setInputFiles(files[2]);
    assert.deepEqual(await submitted(), ['one.png', 'two.png', 'three.png']);
    assert.equal(await page.locator('#gallery figure').count(), 3);
    await gallery.setInputFiles(files[1]);
    assert.deepEqual(await submitted(), ['one.png', 'two.png', 'three.png'], 'Reselecting the same file should not duplicate it');
    await page.getByRole('button', { name: 'Remove two.png', exact: true }).click();
    assert.deepEqual(await submitted(), ['one.png', 'three.png']);
    await gallery.setInputFiles(files[1]);
    assert.deepEqual(await submitted(), ['one.png', 'three.png', 'two.png']);
    await cover.setInputFiles(files[0]);
    await cover.setInputFiles(files[1]);
    assert.deepEqual(await cover.evaluate(input => [...input.files].map(file => file.name)), ['two.png']);
    assert.equal(await page.locator('[data-saved-cover]').isVisible(), false);
    await page.locator('#cover [data-clear-upload]').click();
    assert.equal(await page.locator('[data-saved-cover]').isVisible(), true);
    await page.locator('#gallery [data-clear-upload]').click();
    assert.deepEqual(await submitted(), []);
    assert.equal(await page.locator('#gallery [data-upload-preview]').isVisible(), false);
    await gallery.setInputFiles(files[2]);
    assert.deepEqual(await submitted(), ['three.png']);
    await page.getByRole('button', { name: 'Reset', exact: true }).click();
    await page.waitForTimeout(20);
    assert.deepEqual(await submitted(), []);
    await gallery.setInputFiles(files[0]);
    assert.deepEqual(await submitted(), ['one.png'], 'Reset must also clear accumulated state');
    assert.deepEqual(errors, []);
    console.log('PASS: successive gallery selections submit all files; duplicates, individual removal, clear/reset, and cover replacement verified.');
} finally {
    await browser.close();
    fs.rmSync(temp, { recursive: true, force: true });
}
