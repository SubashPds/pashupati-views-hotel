const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';
const out=path.dirname(fileURLToPath(import.meta.url)), base='http://127.0.0.1:9011';
const browser=await chromium.launch({executablePath:'/usr/bin/google-chrome',headless:true,args:['--no-sandbox']});
const context=await browser.newContext({viewport:{width:1440,height:1000}}), page=await context.newPage();
const results=[], metrics={};
async function go(p){return page.goto(base+p,{waitUntil:'domcontentloaded'});}
async function shot(id){await page.screenshot({path:path.join(out,'screenshots',id+'.png')});}
async function check(id,name,fn){try{results.push({id,name,status:'PASS',details:await fn()});}catch(e){results.push({id,name,status:'FAIL',details:e.message.split('\n').slice(0,6).join('\n')});await shot(id);}console.log(id+' '+results.at(-1).status+' '+name);}
async function post(url,fields,multipart=false,json=false){return page.request.post(base+url,{[multipart?'multipart':'form']:{_token:await page.locator('[name=_token]').first().inputValue(),...fields},maxRedirects:0,headers:json?{Accept:'application/json'}:{}});}
const png=Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=','base64');
try{
 await go('/login');await page.locator('#email').fill('qa@example.test');await page.locator('#password').fill('Qa-Only-2026!');await page.locator('button[type=submit]').click();await page.waitForURL('**/admin/dashboard');
 await check('CSRF-01','State-changing requests without CSRF token are rejected',async()=>{const r=await page.request.post(base+'/admin/blogs',{form:{title:'QA CSRF',content:'QA',is_published:'0'},maxRedirects:0});assert.equal(r.status(),419);});
 await check('VALIDATION-01','Negative room price and out-of-range testimonial rating rejected',async()=>{
  let r=await post('/admin/rooms',{name:'QA Negative',category:'suite',price_per_night:'-1',max_guests:'2',sort_order:'0',is_active:'1'},false,true);assert.equal(r.status(),422);assert.ok((await r.json()).errors.price_per_night);
  r=await post('/admin/testimonials',{author_name:'QA',rating:'6',review:'QA'},false,true);assert.equal(r.status(),422);assert.ok((await r.json()).errors.rating);
 });
 await check('UPLOAD-01','Room cover rejects non-images and images above 4 MB',async()=>{
  const fields={name:'QA invalid cover',category:'suite',price_per_night:'100',max_guests:'2',amenities_raw:'Wi-Fi',sort_order:'0',is_active:'1'};
  let r=await post('/admin/rooms',{...fields,cover_image:{name:'no.txt',mimeType:'text/plain',buffer:Buffer.from('QA only')}},true,true);assert.equal(r.status(),422);assert.ok((await r.json()).errors.cover_image);
  r=await post('/admin/rooms',{...fields,cover_image:{name:'large.png',mimeType:'image/png',buffer:Buffer.concat([png,Buffer.alloc(4*1024*1024)])}},true,true);assert.equal(r.status(),422);assert.ok((await r.json()).errors.cover_image);
 });
 await check('HERO-01','Carousel media create, replace, visibility and delete',async()=>{
  await go('/admin/hero-slides/create');
  let r=await post('/admin/hero-slides',{title:'QA Carousel',sort_order:'0',is_active:'1',media:{name:'hero.png',mimeType:'image/png',buffer:png}},true);assert.equal(r.status(),302);
  await go('/admin/hero-slides');const edit=page.locator('a[href*="/hero-slides/"][href$="/edit"]').last();const url=new URL(await edit.getAttribute('href')).pathname;const id=url.match(/\/(\d+)\/edit/)[1];await go(url);
  r=await post('/admin/hero-slides/'+id,{title:'QA Carousel Updated',sort_order:'1',is_active:'0',_method:'PUT',media:{name:'replacement.png',mimeType:'image/png',buffer:png}},true);assert.equal(r.status(),302);
  await go(url);assert.equal(await page.locator('[name=is_active][type=checkbox]').isChecked(),false);
  r=await post('/admin/hero-slides/'+id,{_method:'DELETE'});assert.equal(r.status(),302);assert.equal((await page.request.get(base+url)).status(),404);
 });
 await check('GALLERY-03','Gallery video upload, toggle, and delete',async()=>{
  await go('/admin/gallery');
  const bytes=await page.evaluate(async()=>{const canvas=document.createElement('canvas');canvas.width=48;canvas.height=48;const stream=canvas.captureStream(10);const rec=new MediaRecorder(stream,{mimeType:'video/webm'});const chunks=[];rec.ondataavailable=e=>chunks.push(e.data);const done=new Promise(resolve=>rec.onstop=resolve);rec.start();canvas.getContext('2d').fillRect(0,0,48,48);await new Promise(r=>setTimeout(r,200));rec.stop();await done;stream.getTracks().forEach(t=>t.stop());return [...new Uint8Array(await new Blob(chunks).arrayBuffer())];});
  assert.equal((await post('/admin/gallery',{title:'QA Video','images[0]':{name:'qa.webm',mimeType:'video/webm',buffer:Buffer.from(bytes)}},true)).status(),302);
  await go('/admin/gallery');const card=page.locator('.group.relative').filter({has:page.getByText('QA Video',{exact:true})});assert.equal(await card.locator('video').count(),1);
  const action=new URL(await card.locator('form').first().getAttribute('action')).pathname;assert.equal((await post(action,{_method:'PATCH'})).status(),302);
  assert.equal((await post(action.replace('/status',''),{_method:'DELETE'})).status(),302);
 });
 await check('BLOG-02','Blog cover preview supports clearing selection',async()=>{
  await go('/admin/blogs/create');await page.locator('#cover_image').setInputFiles({name:'qa.png',mimeType:'image/png',buffer:png});await page.locator('[data-cover-preview]').waitFor({state:'visible'});await page.locator('[data-clear-cover]').click();assert.equal(await page.locator('#cover_image').evaluate(el=>el.files.length),0);
 });
 await check('SETTINGS-02','Validation preserves unsaved changes in other settings tabs',async()=>{
  await go('/admin/settings');await page.locator('[data-tab=hero]').click();await page.locator('[name=hero_body]').fill('QA unsaved body that should survive validation');
  await page.locator('[data-tab=contact]').click();await page.locator('[name=contact_map_location]').fill('x'.repeat(501));await page.getByRole('button',{name:'Save All Settings'}).click();
  await page.getByText('The contact map location field must not be greater than 500 characters.').waitFor();await page.locator('[data-tab=hero]').click();
  assert.equal(await page.locator('[name=hero_body]').inputValue(),'QA unsaved body that should survive validation','Unsaved textarea value reverted after validation error in Map Location.');
 });
 await check('UX-07','Settings fields have programmatically associated labels',async()=>{
  await go('/admin/settings');const unlabeled=await page.locator('main input:not([type=hidden]),main textarea').evaluateAll(els=>els.filter(e=>!e.labels?.length&&!e.getAttribute('aria-label')).map(e=>e.name));metrics.unlabeledSettings=unlabeled;assert.equal(unlabeled.length,0,unlabeled.length+' settings controls lack associated labels.');
 });
 await check('UX-08','Room upload helper text has readable contrast',async()=>{
  await go('/admin/rooms/create');
  const result=await page.getByText('JPG, PNG, WEBP (max 4MB)',{exact:true}).evaluate(el=>{
   const canvas=document.createElement('canvas');canvas.width=canvas.height=1;const ctx=canvas.getContext('2d');
   const rgba=s=>{ctx.clearRect(0,0,1,1);ctx.fillStyle=s;ctx.fillRect(0,0,1,1);const c=ctx.getImageData(0,0,1,1).data;return [c[0],c[1],c[2],c[3]/255];};const chain=[];for(let e=el;e;e=e.parentElement)chain.unshift(e);
   let bg=[255,255,255];for(const e of chain){const c=rgba(getComputedStyle(e).backgroundColor);const a=c[3]??1;bg=bg.map((b,i)=>c[i]*a+b*(1-a));}
   const fg=rgba(getComputedStyle(el).color);const l=c=>c.slice(0,3).map(v=>{v/=255;return v<=.04045?v/12.92:((v+.055)/1.055)**2.4;}).reduce((a,v,i)=>a+v*[.2126,.7152,.0722][i],0);
   const ratio=(Math.max(l(fg),l(bg))+.05)/(Math.min(l(fg),l(bg))+.05);return {foreground:fg,background:bg,ratio,fontSize:getComputedStyle(el).fontSize};
  });metrics.uploadHelperContrast=result;assert.ok(result.ratio>=4.5,`Measured ${result.ratio.toFixed(2)}:1 for ${result.fontSize} helper text.`);
 });
 for(const width of [768,1024]){await check('TABLET-'+width,'Tablet room actions fit available content width at '+width,async()=>{
  await page.setViewportSize({width,height:1024});await go('/admin/rooms');const info=await page.locator('table').evaluate(el=>({table:el.getBoundingClientRect().width,container:el.parentElement.getBoundingClientRect().width,overflow:getComputedStyle(el.parentElement).overflowX}));
  await shot('tablet-rooms-'+width);assert.ok(!(info.table>info.container+1&&info.overflow==='hidden'),JSON.stringify(info));
 });}
 await page.setViewportSize({width:390,height:844});
 await check('UX-09','Mobile menu button exposes expanded state',async()=>{await go('/admin/dashboard');await page.locator('#sidebar-toggle').click();assert.equal(await page.locator('#sidebar-toggle').getAttribute('aria-expanded'),'true');});
}finally{fs.writeFileSync(path.join(out,'followup-results.json'),JSON.stringify({results,metrics},null,2));await browser.close();}
