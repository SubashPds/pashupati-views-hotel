import assert from 'node:assert/strict';
import test from 'node:test';

const listeners = {};
const calls = [];
globalThis.document = {
    addEventListener: (name, listener) => { listeners[name] = listener; },
    querySelectorAll: () => [],
};
globalThis.window = {
    openBooking: () => calls.push('open'),
    closeBooking: () => calls.push('close'),
    closeMobileNav: () => calls.push('mobile'),
    toggleFaq: id => calls.push(id),
    confirm: () => false,
};
await import('../../resources/js/secure-actions.js');

test('trusted delegated controls preserve booking, mobile navigation, and FAQ actions', () => {
    listeners.click({ target: { closest: selector => ['[data-open-booking]', '[data-close-mobile-nav]'].includes(selector) ? {} : null } });
    listeners.click({ target: { closest: selector => selector === '[data-close-booking]' ? {} : null } });
    listeners.click({ target: { closest: selector => selector === '[data-toggle-faq]' ? { dataset: { toggleFaq: 'faq-2' } } : null } });
    assert.deepEqual(calls, ['open', 'mobile', 'close', 'faq-2']);
});

test('currency changes still submit and cancelled delete confirmations prevent submission', () => {
    let submitted = false;
    listeners.change({ target: { matches: () => true, form: { requestSubmit: () => { submitted = true; } } } });
    assert.equal(submitted, true);
    let prevented = false;
    listeners.submit({ target: { dataset: { confirm: 'Delete this item?' } }, preventDefault: () => { prevented = true; } });
    assert.equal(prevented, true);
});
