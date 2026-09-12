const { chromium } = await import(process.env.QA_PLAYWRIGHT_MODULE || '/home/subash/.cache/ms-playwright-go/1.50.1/package/index.mjs');
import fs from 'node:fs';
import path from 'node:path';
import {fileURLToPath} from 'node:url';
import assert from 'node:assert/strict';

const base = 'http://127.0.0.1:9011';
const out = path.dirname(fileURLToPath(import.meta.url));
fs.mkdirSync(path.join(out, 'screenshots'), { recursive: true });
const browser = await chromium.launch({ executablePath: '/usr/bin/google-chrome', headless: true, args: ['--no-sandbox'] });
const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
const page = await context.newPage();
const results = [], errors = [], inventory = [];
page.on('pageerror', e => errors.push({ url: page.url().replace(base, ''), error: e.message }));
const png = Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=', 'base64');
async function go(url) { return page.goto(base + url, { waitUntil: 'domcontentloaded', timeout: 30000 }); }
async function shot(name) { await page.screenshot({ path: path.join(out, 'screenshots', name + '.png') }); }
async function check(id, name, fn) {
    try { const details = await fn(); results.push({ id, name, status: 'PASS', details }); }
    catch (e) { results.push({ id, name, status: 'FAIL', details: e.message.split('\n').slice(0, 8).join('\n') }); await shot(id).catch(() => {}); }
    fs.writeFileSync(path.join(out, 'browser-results.json'), JSON.stringify({ results, inventory, errors }, null, 2));
    console.log(`${id} ${results.at(-1).status} ${name}`);
}
async function login(email = 'qa@example.test', password = 'Qa-Only-2026!') {
    await go('/login'); await page.locator('#email').fill(email); await page.locator('#password').fill(password);
    await Promise.all([page.waitForURL(u => !u.pathname.endsWith('/login')), page.locator('button[type=submit]').click()]);
}
async function submit(url, fields = {}, multipart = false) {
    const token = await page.locator('input[name=_token]').first().inputValue();
    return page.request.post(base + url, { [multipart ? 'multipart' : 'form']: { _token: token, ...fields }, maxRedirects: 0 });
}
async function idFor(section, text) {
    await go('/admin/' + section);
    return page.locator(`a[href*="/${section}/"][href$="/edit"]`).filter({ hasText: 'Edit' }).evaluateAll((links, text) => {
        const link = links.find(link => (link.closest('tr') || link.closest('.rounded-2xl'))?.textContent.includes(text));
        return link?.getAttribute('href').match(/\/(\d+)\/edit/)[1];
    }, text);
}
const roomData = { name: 'QA Garden Room', category: 'deluxe', price_per_night: '4500', max_guests: '2', is_active: '1', sort_order: '10' };
let roomId, blogId, packageId;
try {
    await check('AUTH-01', 'Unauthenticated admin access redirects to login', async () => { await go('/admin/rooms'); assert.equal(new URL(page.url()).pathname, '/login'); });
    await check('AUTH-02', 'Incorrect credentials show a useful error', async () => {
        await page.locator('#email').fill('qa@example.test'); await page.locator('#password').fill('incorrect');
        await page.locator('button[type=submit]').click(); await page.getByText('The provided credentials do not match our records.').waitFor();
    });
    for (const [id, email, message] of [['AUTH-03','inactive@example.test','deactivated'],['AUTH-04','guest@example.test','Only super admins']]) {
        await check(id, 'Reject ' + email.split('@')[0] + ' account', async () => {
            await go('/login'); await page.locator('#email').fill(email); await page.locator('#password').fill('Qa-Only-2026!');
            await page.locator('button[type=submit]').click(); await page.getByText(message, { exact: false }).waitFor();
        });
    }
    await check('AUTH-05', 'Active admin can log in', async () => { await login(); assert.ok(page.url().endsWith('/admin/dashboard')); });
    await shot('desktop-dashboard');
    for (const section of ['dashboard','rooms','packages','experiences','hero-slides','gallery','services','testimonials','blogs','enquiries','settings']) {
        await check('PAGE-' + section, 'Open admin ' + section, async () => {
            const response = await go('/admin/' + section); assert.equal(response.status(), 200);
            const data = await page.evaluate(() => ({
                title: document.title,
                unlabeled: [...document.querySelectorAll('input:not([type=hidden]),select,textarea')].filter(el => !el.labels?.length && !el.getAttribute('aria-label') && !el.getAttribute('aria-labelledby')).map(el => el.name),
            })); inventory.push({ section, ...data });
        });
    }
    await check('ROOM-00', 'Create room with optional amenities left blank', async () => {
        await go('/admin/rooms/create'); await page.locator('[name=name]').fill('QA Blank Amenities'); await page.locator('[name=price_per_night]').fill('4500');
        const [response] = await Promise.all([page.waitForResponse(r => r.request().method()==='POST' && r.url().endsWith('/admin/rooms')), page.getByRole('button',{name:'Create Room',exact:true}).click()]);
        assert.equal(response.status(),302,'Blank optional amenities produced HTTP '+response.status());
    });
    await check('ROOM-01', 'Create room with image preview and remove one selected image', async () => {
        await go('/admin/rooms/create');
        await page.locator('[name="gallery_images[]"]').setInputFiles([{name:'one.png',mimeType:'image/png',buffer:png},{name:'two.png',mimeType:'image/png',buffer:png}]);
        assert.equal(await page.locator('[data-remove-preview]').count(), 2);
        await page.locator('[data-remove-preview]').first().click();
        assert.equal(await page.locator('[name="gallery_images[]"]').evaluate(el => el.files.length), 1);
        await page.locator('[name=amenities_raw]').fill('Wi-Fi'); await page.locator('[name=name]').fill(roomData.name); await page.locator('[name=price_per_night]').fill('4500');
        await page.getByRole('button',{name:'Create Room',exact:true}).click(); await page.waitForURL('**/admin/rooms');
        roomId = await idFor('rooms', roomData.name); assert.ok(roomId);
    });
    await check('ROOM-02', 'Edit and toggle room visibility', async () => {
        await go(`/admin/rooms/${roomId}/edit`);
        assert.equal((await submit(`/admin/rooms/${roomId}`, { ...roomData, _method:'PUT', tagline:'QA updated' })).status(), 302);
        await go('/admin/rooms'); assert.equal((await submit(`/admin/rooms/${roomId}/status`, {_method:'PATCH'})).status(), 302);
        await go(`/admin/rooms/${roomId}/edit`); assert.equal(await page.locator('input[type=checkbox][name=is_active]').isChecked(), false);
    });
    await check('ROOM-03', 'Duplicate room names produce validation instead of server error', async () => {
        await go('/admin/rooms/create'); const response = await submit('/admin/rooms',roomData);
        assert.equal(response.status(), 302, 'Duplicate existing name produced HTTP '+response.status()+' (unique room slug).');
    });
    await check('ROOM-04', 'Room gallery rejects non-image uploads', async () => {
        await go('/admin/rooms/create');
        const response = await page.request.post(base + '/admin/rooms', {headers:{Accept:'application/json'}, multipart:{_token:await page.locator('[name=_token]').first().inputValue(), ...roomData,name:'QA Invalid Gallery','gallery_images[0]':{name:'qa-not-image.txt',mimeType:'text/plain',buffer:Buffer.from('Harmless QA upload; not an image.')}}});
        assert.equal(response.status(),422);
        assert.ok((await response.json()).errors['gallery_images.0']);
        await go('/admin/rooms'); assert.equal(await page.getByText('QA Invalid Gallery',{exact:true}).count(),0);
    });
    await check('FORM-01', 'Package price accepts decimal currency values', async () => {
        await go('/admin/packages/create'); await page.locator('[name=price_from]').fill('100.50');
        const validity = await page.locator('[name=price_from]').evaluate(el => ({ step:el.getAttribute('step'), stepMismatch:el.validity.stepMismatch }));
        assert.equal(validity.stepMismatch,false,JSON.stringify(validity));
    });
    await check('PACKAGE-01', 'Create and edit package', async () => {
        await go('/admin/packages/create'); const fields = {name:'QA Weekend Package',price_from:'5000',min_guests:'1',max_guests:'2',sort_order:'0',is_active:'1'};
        assert.equal((await submit('/admin/packages',fields)).status(),302); packageId = await idFor('packages',fields.name); assert.ok(packageId);
        assert.equal((await submit(`/admin/packages/${packageId}`,{...fields,tagline:'QA edited',_method:'PUT'})).status(),302);
    });
    await check('PACKAGE-02', 'Package maximum guests cannot be below minimum', async () => {
        await go(`/admin/packages/${packageId}/edit`);
        const response=await page.request.post(base+`/admin/packages/${packageId}`,{headers:{Accept:'application/json'},form:{_token:await page.locator('[name=_token]').first().inputValue(),name:'QA Weekend Package',min_guests:'5',max_guests:'2',sort_order:'0',is_active:'1',_method:'PUT'}});
        assert.equal(response.status(),422);assert.ok((await response.json()).errors.max_guests);
        await go(`/admin/packages/${packageId}/edit`);
        assert.ok(Number(await page.locator('[name=max_guests]').inputValue()) >= Number(await page.locator('[name=min_guests]').inputValue()),'Saved values must retain the valid range.');
    });
    for (const [section,fields] of [['experiences',{title:'QA Experience',description:'QA description',icon:'✨',sort_order:'0'}],['services',{title:'QA Service',description:'QA description',icon:'☕',sort_order:'0'}],['testimonials',{author_name:'QA Reviewer',rating:'5',review:'QA review',sort_order:'0'}]]) {
        await check('CRUD-'+section, 'Create, edit, toggle, and delete '+section, async () => {
            await go(`/admin/${section}/create`); assert.equal((await submit(`/admin/${section}`,fields)).status(),302);
            const id = await idFor(section,fields.title || fields.author_name); assert.ok(id);
            assert.equal((await submit(`/admin/${section}/${id}`,{...fields,_method:'PUT'})).status(),302);
            assert.equal((await submit(`/admin/${section}/${id}/status`,{_method:'PATCH'})).status(),302);
            assert.equal((await submit(`/admin/${section}/${id}`,{_method:'DELETE'})).status(),302);
        });
    }
    await check('GALLERY-01','Upload image and reject invalid gallery media',async()=>{
        await go('/admin/gallery');
        assert.equal((await submit('/admin/gallery',{title:'QA photo','images[0]':{name:'qa.png',mimeType:'image/png',buffer:png}},true)).status(),302);
        await go('/admin/gallery'); await page.getByText('QA photo',{exact:true}).waitFor();
        const response=await submit('/admin/gallery',{title:'QA invalid media','images[0]':{name:'invalid.txt',mimeType:'text/plain',buffer:Buffer.from('QA only')}},true);
        assert.equal(response.status(),302); await go('/admin/gallery'); assert.equal(await page.getByText('QA invalid media',{exact:true}).count(),0);
    });
    await check('GALLERY-02','Existing gallery items have an edit action',async()=>{
        await go('/admin/gallery'); assert.ok(await page.locator('a[href*="/gallery/"][href$="/edit"],button:has-text("Edit")').count(),'No edit control exists for uploaded gallery caption, badge, or section.');
    });
    await check('BLOG-01','Create draft, publish, edit, and delete blog',async()=>{
        await go('/admin/blogs/create'); const data={title:'QA Blog',content:'QA article content',is_published:'0'};
        assert.equal((await submit('/admin/blogs',data)).status(),302); blogId=await idFor('blogs','QA Blog');
        assert.equal((await page.request.get(base+'/blogs/qa-blog')).status(),404);
        await submit(`/admin/blogs/${blogId}`,{...data,is_published:'1',_method:'PUT'});
        assert.equal((await page.request.get(base+'/blogs/qa-blog')).status(),200);
        await submit(`/admin/blogs/${blogId}`,{_method:'DELETE'}); assert.equal((await page.request.get(base+'/blogs/qa-blog')).status(),404);
    });
    await check('ENQUIRY-01','Status filter survives pagination',async()=>{
        await go('/admin/enquiries?status=new'); const next = page.locator('a[rel=next]:visible');
        assert.match(await next.getAttribute('href'),/status=new/); await next.click(); assert.match(page.url(),/status=new/);
    });
    await check('ENQUIRY-02','Opening enquiry marks it read; status update persists',async()=>{
        await go('/admin/enquiries?status=new'); const link=page.locator('table a').filter({hasText:'View'}).first();
        const url=new URL(await link.getAttribute('href')).pathname; await link.click();
        assert.equal(await page.locator('[name=status]').inputValue(),'read');
        assert.equal((await submit(url+'/status',{status:'replied',_method:'PATCH'})).status(),302);
        await go(url); assert.equal(await page.locator('[name=status]').inputValue(),'replied');
    });
    await check('SETTINGS-01','Map location saves and renders in public map',async()=>{
        await go('/admin/settings'); await submit('/admin/settings',{contact_map_location:'27.7100, 85.3500'});
        await go('/admin/settings'); assert.equal(await page.locator('[name=contact_map_location]').inputValue(),'27.7100, 85.3500');
        const response=await page.request.get(base+'/'); assert.ok((await response.text()).includes('27.7100%2C%2085.3500'));
    });
    await check('UX-01','Sidebar keeps Rooms active while editing',async()=>{
        await go(`/admin/rooms/${roomId}/edit`); const classes=await page.locator('#sidebar a[href$="/admin/rooms"]').getAttribute('class');
        assert.ok(classes.includes('bg-violet-600/20'),'Rooms sidebar item is not active on its edit page.');
    });
    await check('UX-02','Required fields have native required semantics',async()=>{
        await go('/admin/rooms/create'); assert.equal(await page.locator('[name=name]').getAttribute('required') !== null,true,'Room Name has a required star but no required attribute.');
    });
    await check('UX-03','Room upload controls are keyboard reachable',async()=>{
        assert.notEqual(await page.locator('[name=cover_image]').evaluate(el=>getComputedStyle(el).display),'none','Room cover input is display:none and its wrapping label has no keyboard activation.');
    });
    await page.setViewportSize({width:390,height:844});
    await check('UX-04','Mobile sidebar closes with Escape',async()=>{
        await go('/admin/dashboard'); await page.locator('#sidebar-toggle').click(); await page.keyboard.press('Escape');
        assert.ok((await page.locator('#sidebar').getAttribute('class')).includes('-translate-x-full'),'Escape did not close the mobile sidebar.');
        assert.equal(await page.locator('#sidebar-overlay').isVisible(), false);
    });
    await check('UX-05','Closed mobile sidebar does not receive keyboard focus',async()=>{
        await go('/admin/dashboard'); await page.keyboard.press('Tab');
        assert.equal(await page.evaluate(()=>!!document.activeElement.closest('#sidebar')),false,'First Tab focuses a link inside the offscreen sidebar.');
    });
    for (const section of ['rooms','packages','experiences','services','testimonials','blogs','enquiries']) {
        await check('MOBILE-'+section,'Mobile '+section+' actions are reachable without clipping',async()=>{
            await go('/admin/'+section); await page.waitForTimeout(200);
            await page.locator('table').evaluateAll(tables=>tables.forEach(table=>{table.parentElement.scrollLeft=table.parentElement.scrollWidth;}));
            const clipped=await page.locator('table').evaluateAll(tables=>tables.flatMap(table=>[...table.querySelectorAll('a,button')].filter(el=>{
                let parent=el.parentElement; const rect=el.getBoundingClientRect();
                while(parent){ const c=getComputedStyle(parent); if(c.overflowX==='auto' || c.overflowX==='scroll') return false; const b=parent.getBoundingClientRect(); if(c.overflowX==='hidden' && (rect.right>b.right+1 || rect.left<b.left-1)) return true; parent=parent.parentElement; }
                return false;
            }).map(el=>el.textContent.trim())));
            const tableAction=page.locator('table a').filter({hasText:/Edit|View/}).first();
            if(await tableAction.count()) await tableAction.click({trial:true});
            await shot('mobile-'+section); assert.equal(clipped.length,0,'Clipped actions: '+[...new Set(clipped)].join(', '));
        });
    }
    await check('UX-06','Mobile save displays success feedback',async()=>{
        await go('/admin/blogs/create'); await page.locator('[name=title]').fill('QA Mobile Draft'); await page.locator('[name=content]').fill('QA mobile article');
        await page.getByRole('button',{name:'Create Blog',exact:true}).click(); await page.waitForURL('**/admin/blogs');
        await shot('mobile-save-feedback'); assert.equal(await page.getByText('Blog created.',{exact:true}).isVisible(),true,'Success message exists but is hidden below 640px.');
    });
    await check('AUTH-06','Logout ends session and blocks admin',async()=>{
        const response=await submit('/logout'); assert.equal(response.status(),302); await go('/admin/dashboard'); assert.ok(page.url().endsWith('/login'));
    });
} finally {
    fs.writeFileSync(path.join(out,'browser-results.json'),JSON.stringify({results,inventory,errors},null,2));
    await browser.close();
}
