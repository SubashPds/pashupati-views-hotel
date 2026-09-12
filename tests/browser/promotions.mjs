// Run against the isolated QA fixture at port 9011; never the hotel database.
import assert from 'node:assert/strict';
const {chromium} = await import('/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
const browser = await chromium.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
const page = await browser.newPage({viewport:{width:1440,height:1000}});
const base = 'http://127.0.0.1:9011';
const go = path => page.goto(base+path,{waitUntil:'domcontentloaded'});
const visibleTitle = () => page.locator('[data-promotion-card][data-stack-depth="0"]:not([hidden]) h2').innerText();
const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=','base64');
try {
 await go('/login');
 await page.locator('#email').fill('qa@example.test'); await page.locator('#password').fill('Qa-Only-2026!');
 await page.locator('button[type=submit]').click(); await page.waitForURL('**/admin/dashboard');
 for (const [title,order,active] of [['QA First offer',0,true],['QA Second offer',1,true],['QA Third offer',2,true],['QA Fourth offer',3,true],['QA Hidden offer',4,false]]) {
  await go('/admin/promotions/create');
  await page.locator('#title').fill(title); await page.locator('#sort_order').fill(String(order));
  await page.locator('#description').fill('Discover your next stay. '+ 'Offer details. '.repeat(50));
  if(!active) await page.locator('[name=is_active][type=checkbox]').uncheck();
  if(order===0) {
   await page.locator('#offer_image').setInputFiles({name:'test.png',mimeType:'image/png',buffer:png});
   await page.locator('[data-offer-preview]').waitFor({state:'visible'});
   await page.locator('[data-clear-offer-image]').click();
   assert.equal(await page.locator('#offer_image').evaluate(el=>el.files.length),0);
   await page.locator('#offer_image').setInputFiles({name:'test.png',mimeType:'image/png',buffer:png});
  }
  await page.getByRole('button',{name:'Save promotion'}).click(); await page.waitForURL('**/admin/promotions');
 }
 assert.equal(await page.locator('article').count(),5);
 console.log('PASS multiple cards created with image preview and visibility controls');
 await go('/'); await page.locator('#promotion-dialog[open]').waitFor();
 assert.equal(await visibleTitle(),'QA First offer');
 assert.equal(await page.locator('[data-promotion-card]').count(),4);
 assert.equal(await page.evaluate(()=>document.body.style.overflow),'hidden');
 assert.equal(await page.locator('[data-stack-depth="0"]:not([hidden]) [data-dismiss-promotion]').evaluate(el=>el===document.activeElement),true);
 await page.keyboard.press('Tab');
 assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.contains(document.activeElement)),true);
 console.log('PASS automatically opens, only active cards, focus stays inside');
 assert.equal(await page.locator('[data-promotion-next], [data-promotion-prev], [data-promotion-dot]').count(),0);
 assert.equal(await page.locator('[data-promotion-card][inert]').count(),3);
 await page.locator('[data-stack-depth="0"]:not([hidden]) [data-dismiss-promotion]').click();
 assert.equal(await visibleTitle(),'QA Second offer');
 assert.equal(await page.locator('[data-promotion-card]:not([hidden])').count(),3);
 assert.equal(await page.locator('[data-stack-depth="0"]:not([hidden]) [data-dismiss-promotion]').evaluate(el=>el===document.activeElement),true);
 for (const title of ['QA Third offer','QA Fourth offer']) {
  await page.locator('[data-stack-depth="0"]:not([hidden]) [data-dismiss-promotion]').click();
  assert.equal(await visibleTitle(),title);
 }
 console.log('PASS stacked cards replace navigation; dismissing top reveals and focuses next');
 for(const [width,height] of [[375,667],[768,900],[1440,1000]]) {
  await page.setViewportSize({width,height});
  const rect = await page.locator('#promotion-dialog').boundingBox();
  assert.ok(rect.x>=0 && rect.y>=0 && rect.x+rect.width<=width && rect.y+rect.height<=height);
  assert.ok(Math.abs((rect.x+rect.width/2)-width/2)<2);
  assert.ok(Math.abs((rect.y+rect.height/2)-height/2)<2);
  const header = await page.locator('[data-stack-depth="0"]:not([hidden]) header').boundingBox();
  await page.locator('[data-stack-depth="0"]:not([hidden]) [data-promotion-body]').evaluate(el=>el.scrollTop=el.scrollHeight);
  assert.deepEqual(await page.locator('[data-stack-depth="0"]:not([hidden]) header').boundingBox(),header);
 }
 await page.setViewportSize({width:375,height:667});
 await page.screenshot({path:'/tmp/promotion-popup-mobile.png'});
 console.log('PASS centered at phone/tablet/desktop sizes with fixed card header');
 await page.locator('[data-stack-depth="0"]:not([hidden]) [data-dismiss-promotion]').click(); assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.open),false);
 assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
 await page.reload({waitUntil:'domcontentloaded'});
 assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.open),false);
 assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
 console.log('PASS final card closes the stack, scrolling restored, reload does not reopen popup');
 // Simulate a fresh visit to exercise the booking action independently.
 await page.evaluate(()=>sessionStorage.removeItem('hotel.promotions.seen'));
 await page.reload({waitUntil:'domcontentloaded'}); await page.locator('#promotion-dialog[open]').waitFor();
 await page.locator('[data-stack-depth="0"]:not([hidden]) [data-promotion-body]').evaluate(el=>el.scrollTop=el.scrollHeight);
 await page.locator('[data-promotion-card][data-stack-depth="0"]:not([hidden]) [data-promotion-book]').click();
 await page.locator('#booking-dialog[open]').waitFor();
 assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.open),false);
 await page.keyboard.press('Escape');
 assert.equal(await page.evaluate(()=>document.body.style.overflow),'');
 console.log('PASS enquiry button opens booking without stacked popups or locked page');
 await go('/blogs');
 assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.open),false);
 console.log('PASS navigating to blogs does not reopen popup');
 await page.evaluate(()=>sessionStorage.removeItem('hotel.promotions.seen'));
 await page.reload({waitUntil:'domcontentloaded'}); await page.locator('#promotion-dialog[open]').waitFor();
 await page.locator('[data-stack-depth="0"]:not([hidden]) [data-close-promotions]').click();
 assert.equal(await page.locator('#promotion-dialog').evaluate(el=>el.open),false);
 console.log('PASS blog entry also opens popup and close button works');
} finally {await browser.close();}
