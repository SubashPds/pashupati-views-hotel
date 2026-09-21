import { initializeDetailPhotos } from './detail-photos';

let loading = false;

document.querySelectorAll('[data-package-details]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (loading || document.querySelector('dialog[open]')) return;
        const error = button.parentElement.querySelector('[data-package-details-error]');
        const label = button.textContent;
        let dialog;
        loading = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Loading details…';
        error.hidden = true;

        try {
            const response = await fetch(button.dataset.packageDetailsUrl, {
                headers: { Accept: 'text/html' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: AbortSignal.timeout(15000),
            });
            if (!response.ok) throw new Error('Package details request failed');

            const template = document.createElement('template');
            template.innerHTML = await response.text();
            dialog = template.content.querySelector('.package-details-dialog');
            if (!dialog || dialog.id !== button.dataset.packageDetails) throw new Error('Missing package details');
            // Another action may have opened a dialog while the request was pending.
            if (document.querySelector('dialog[open]')) return;

            document.body.append(dialog);
            initializeDetailPhotos(dialog);
            dialog.querySelector('[data-close-package]').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target !== dialog) return;
                const rect = dialog.getBoundingClientRect();
                if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
                    dialog.close();
                }
            });
            const previousOverflow = document.body.style.overflow;
            dialog.addEventListener('close', () => {
                if (!document.querySelector('dialog[open]')) {
                    document.body.style.overflow = previousOverflow;
                    if (!dialog.dataset.enquiring) button.focus({ preventScroll: true });
                }
                dialog.remove();
            });
            dialog.showModal();
            dialog.querySelector('[data-package-body]').scrollTop = 0;
            document.body.style.overflow = 'hidden';
        } catch {
            dialog?.remove();
            error.textContent = 'Package details could not be loaded. Please try again.';
            error.hidden = false;
        } finally {
            loading = false;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.textContent = label;
        }
    });
});

// Delegation also handles enquiry links in details loaded after page startup.
document.addEventListener('click', (event) => {
    const link = event.target.closest('[data-package-enquiry]');
    if (!link) return;
    const dialog = link.closest('dialog');
    if (dialog) {
        dialog.dataset.enquiring = 'true';
        dialog.close();
    }
    const category = document.getElementById('c-cat');
    const message = document.getElementById('c-msg');
    const packageOption = category && Array.from(category.options).find((option) => option.value === `package:${link.dataset.packageId}`);
    if (category) category.value = packageOption ? packageOption.value : 'Packages';
    if (message && !packageOption) {
        const enquiry = `I'm interested in the ${link.dataset.packageEnquiry} package.`;
        if (!message.value.includes(enquiry)) {
            message.value = message.value.trim() ? `${message.value}\n\n${enquiry}` : enquiry;
        }
    }
    requestAnimationFrame(() => document.getElementById('c-name')?.focus({ preventScroll: true }));
});
