// Uses the isolated currency QA fixture; country headers are recognized only by that fixture.
import assert from 'node:assert/strict';
const {chromium}=await import('/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const browser=await chromium.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
const page=await browser.newPage({viewport:{width:1440,height:1000}});
const base='http://127.0.0.1:9011';
const go=path=>page.goto(base+path,{waitUntil:'domcontentloaded'});
try {
 await go('/login'); await page.locator('#email').fill('qa@example.test'); await page.locator('#password').fill('Qa-Only-2026!');
 await page.locator('button[type=submit]').click(); await page.waitForURL('**/admin/dashboard');
 await go('/admin/settings'); await page.locator('[data-tab=currency]').click();
 await page.locator('#setting_currency_npr_per_inr').fill('1.6');
 await page.locator('#setting_currency_npr_per_usd').fill('160');
 await page.getByRole('button',{name:'Save All Settings'}).click(); await page.waitForLoadState('domcontentloaded');
 assert.equal(await page.locator('#tab-currency').isVisible(),true);
 assert.equal(await page.locator('#setting_currency_npr_per_usd').inputValue(),'160');
 console.log('PASS dashboard currency rates save and selected tab stays open');
 for(const [country,code,roomPrice,packagePrice] of [['NP','NPR','1,600','3,200'],['IN','INR','1,000.00','2,000.00'],['US','USD','10.00','20.00']]) {
  await page.setExtraHTTPHeaders({'X-QA-Country':country});
  await go('/');
  const room=page.locator('.room-card').filter({hasText:'QA Currency Room'});
  assert.ok((await room.innerText()).includes(code+' '+roomPrice));
  await room.locator('[data-room-details]').click();
  const roomDialog=page.locator('.room-details-dialog[open]');
  assert.ok((await roomDialog.innerText()).replace(/\s/g,'').includes(code+roomPrice));
  await roomDialog.locator('[data-close-room]').click();
  const pack=page.locator('.package-card').filter({hasText:'QA Currency Package'});
  assert.ok((await pack.innerText()).includes(code+' '+packagePrice+' / person'));
  await pack.locator('[data-package-details]').click();
  const packageDialog=page.locator('.package-details-dialog[open]');
  assert.ok((await packageDialog.innerText()).includes(code+' '+packagePrice+' / person'));
  await packageDialog.locator('[data-close-package]').click();
  console.log('PASS '+country+' → '+code+' across room/package cards and details');
 }
 await page.setViewportSize({width:375,height:812});
 await go('/');
 const room=page.locator('.room-card').filter({hasText:'QA Currency Room'});
 await room.locator('[data-room-details]').click();
 const rect=await page.locator('.room-details-dialog[open]').boundingBox();
 assert.ok(rect.x>=0&&rect.x+rect.width<=375);
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
 console.log('PASS converted mobile room details fit the screen');
} finally {await browser.close();}
