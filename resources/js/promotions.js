const dialog = document.getElementById('promotion-dialog');
const seenKey = 'hotel.promotions.seen';

if (dialog) {
    const cards = [...dialog.querySelectorAll('[data-promotion-card]')];
    let remaining = [...cards];
    let previousOverflow = '';
    let previousFocus;

    function dismissTop() {
        const dismissed = remaining.shift();
        dismissed.hidden = true;
        dismissed.inert = true;
        dismissed.setAttribute('aria-hidden', 'true');
        if (!remaining.length) {
            close();
            return;
        }
        remaining.forEach((card, index) => {
            card.hidden = index > 2;
            card.inert = index > 0;
            card.setAttribute('aria-hidden', String(index > 0));
            card.dataset.stackDepth = String(Math.min(index, 2));
            card.style.setProperty('--stack-depth', Math.min(index, 2));
            card.style.setProperty('--stack-order', remaining.length - index);
        });
        const top = remaining[0];
        top.querySelector('[data-promotion-body]').scrollTop = 0;
        top.querySelector('[data-dismiss-promotion]').focus({preventScroll: true});
        dialog.querySelector('[data-promotion-status]').textContent = `${top.querySelector('h2').textContent}. ${remaining.length} ${remaining.length === 1 ? 'offer remains' : 'offers remain'}.`;
    }

    function close() {
        dialog.close();
        if (!document.querySelector('dialog[open]')) document.body.style.overflow = previousOverflow;
    }

    dialog.querySelectorAll('[data-close-promotions]').forEach(button => button.addEventListener('click', close));
    cards.forEach(card => card.querySelector('[data-dismiss-promotion]')?.addEventListener('click', dismissTop));
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) close();
    });
    dialog.addEventListener('close', () => {
        if (!document.querySelector('dialog[open]')) {
            document.body.style.overflow = previousOverflow;
            previousFocus?.focus({preventScroll: true});
        }
    });
    dialog.querySelectorAll('[data-promotion-link]').forEach(link => link.addEventListener('click', close));
    dialog.querySelectorAll('[data-promotion-book]').forEach(button => button.addEventListener('click', () => {
        close();
        window.openBooking();
    }));

    function open() {
        if (document.querySelector('dialog[open]')) return;
        try {
            if (sessionStorage.getItem(seenKey) === '1') return;
        } catch {
            // Keep the popup usable when browser storage is unavailable.
        }
        previousOverflow = document.body.style.overflow;
        previousFocus = document.activeElement;
        dialog.showModal();
        document.body.style.overflow = 'hidden';
        try {
            // Record display immediately, including reloads before dismissal.
            sessionStorage.setItem(seenKey, '1');
        } catch {
            // Storage restrictions must not interrupt navigation or dismissal.
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', open, {once: true});
    else open();
}
