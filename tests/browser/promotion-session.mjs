// Browser-only regression fixture: no application server or database required.
import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
const {chromium} = await import('/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const source = await readFile(new URL('../../resources/js/promotions.js', import.meta.url), 'utf8');
const html = `<!doctype html><html><body>
<a href="/blogs">Blogs</a>
<dialog id="promotion-dialog">
<button data-close-promotions autofocus>Close</button>
<div data-promotion-body><article data-promotion-card><h2>Offer</h2></article></div>
<p data-promotion-status role="status"></p>
</dialog>
<script type="module" src="/promotions.js"></script>
</body></html>`;
const browser = await chromium.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
async function newVisit() {
 const context = await browser.newContext();
 await context.route('http://hotel.test/**', route => route.fulfill({
  contentType: route.request().url().endsWith('.js') ? 'text/javascript' : 'text/html',
  body: route.request().url().endsWith('.js') ? source : html,
 }));
 const page = await context.newPage();
 await page.goto('http://hotel.test/', {waitUntil:'domcontentloaded'});
 return {context,page};
}
const open = page => page.locator('#promotion-dialog').evaluate(el=>el.open);
try {
 let {context,page} = await newVisit();
 assert.equal(await open(page),true);
 await page.reload({waitUntil:'domcontentloaded'});
 assert.equal(await open(page),false);
 assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
 console.log('PASS reload before dismissal does not reopen or lock scrolling');
 await context.close();
 ({context,page} = await newVisit());
 assert.equal(await open(page),true);
 await page.keyboard.press('Escape');
 assert.equal(await open(page),false);
 await page.reload({waitUntil:'domcontentloaded'});
 assert.equal(await open(page),false);
 await page.getByRole('link',{name:'Blogs'}).click();
 await page.waitForURL('**/blogs');
 assert.equal(await open(page),false);
 console.log('PASS Escape, reload and navigation retain seen state');
 await context.close();
 ({context,page} = await newVisit());
 assert.equal(await open(page),true);
 await page.locator('[data-close-promotions]').click();
 await page.reload({waitUntil:'domcontentloaded'});
 assert.equal(await open(page),false);
 console.log('PASS fresh session shows popup; close button persists across reload');
 await context.close();
} finally {await browser.close();}
