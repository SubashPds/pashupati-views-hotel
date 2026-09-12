const sidebar = document.getElementById('sidebar');
const toggle = document.getElementById('sidebar-toggle');
if (sidebar && toggle) {
    const overlay = document.getElementById('sidebar-overlay');
    const content = document.getElementById('admin-content');
    const mobile = window.matchMedia('(max-width: 767px)');
    let opened = false;
    const focusable = () => [...sidebar.querySelectorAll('a[href],button:not(:disabled),input:not(:disabled),select,textarea,[tabindex="0"]')]
        .filter(el => el.getClientRects().length && getComputedStyle(el).visibility !== 'hidden');

    function setOpen(value, restoreFocus = false) {
        opened = mobile.matches && value;
        content.inert = opened;
        sidebar.inert = mobile.matches && !opened;
        sidebar.classList.toggle('-translate-x-full', !opened);
        sidebar.classList.toggle('invisible', mobile.matches && !opened);
        overlay.classList.toggle('hidden', !opened);
        toggle.setAttribute('aria-expanded', String(opened));
        toggle.setAttribute('aria-label', opened ? 'Close sidebar' : 'Open sidebar');
        if (mobile.matches && !opened) sidebar.setAttribute('aria-hidden', 'true');
        else sidebar.removeAttribute('aria-hidden');
        if (opened) focusable()[0]?.focus();
        else if (restoreFocus && mobile.matches) toggle.focus();
    }

    toggle.addEventListener('click', () => setOpen(!opened, true));
    overlay.addEventListener('click', () => setOpen(false, true));
    sidebar.querySelector('[data-sidebar-close]').addEventListener('click', () => setOpen(false, true));
    document.addEventListener('keydown', event => {
        if (!opened) return;
        if (event.key === 'Escape') { event.preventDefault(); setOpen(false, true); }
        if (event.key === 'Tab') {
            const items = focusable();
            if (event.shiftKey && document.activeElement === items[0]) { event.preventDefault(); items.at(-1)?.focus(); }
            else if (!event.shiftKey && document.activeElement === items.at(-1)) { event.preventDefault(); items[0]?.focus(); }
        }
    });
    mobile.addEventListener('change', () => setOpen(false));
    setOpen(false);
}
