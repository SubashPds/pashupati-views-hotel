// Keep actions in trusted scripts so CSP can reject inline event handlers.
document.addEventListener('click', event => {
    if (event.target.closest('[data-open-booking]')) window.openBooking?.();
    if (event.target.closest('[data-close-booking]')) window.closeBooking?.();
    if (event.target.closest('[data-close-mobile-nav]')) window.closeMobileNav?.();
    const faq = event.target.closest('[data-toggle-faq]');
    if (faq) window.toggleFaq?.(faq.dataset.toggleFaq);
});
document.addEventListener('change', event => {
    if (event.target.matches('[data-submit-on-change]')) event.target.form?.requestSubmit();
});
document.addEventListener('submit', event => {
    const message = event.target.dataset.confirm;
    if (message && !window.confirm(message)) event.preventDefault();
});
document.querySelectorAll('[data-admin-image-fallback]').forEach(image => {
    const fallback = () => { image.hidden = true; };
    image.addEventListener('error', fallback);
    if (image.complete && !image.naturalWidth) fallback();
});
