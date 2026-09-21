import { initializeDetailPhotos } from './detail-photos';

let loading = false;

document.querySelectorAll('[data-room-details]').forEach((button) => {
    button.addEventListener('click', async () => {
        if (loading || document.querySelector('dialog[open]')) return;
        const error = button.parentElement.querySelector('[data-room-details-error]');
        const label = button.textContent;
        let dialog;
        loading = true;
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');
        button.textContent = 'Loading details…';
        error.hidden = true;

        try {
            const response = await fetch(button.dataset.roomDetailsUrl, {
                headers: { Accept: 'text/html' },
                credentials: 'same-origin',
                cache: 'no-store',
                signal: AbortSignal.timeout(15000),
            });
            if (!response.ok) throw new Error('Room details request failed');

            const template = document.createElement('template');
            template.innerHTML = await response.text();
            dialog = template.content.querySelector('.room-details-dialog');
            if (!dialog || dialog.id !== button.dataset.roomDetails) throw new Error('Missing room details');
            // Another action may have opened a dialog while the request was pending.
            if (document.querySelector('dialog[open]')) return;

            document.body.append(dialog);
            initializeDetailPhotos(dialog);
            dialog.querySelector('[data-close-room]').addEventListener('click', () => dialog.close());
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
                    button.focus({ preventScroll: true });
                }
                dialog.remove();
            });
            dialog.showModal();
            dialog.querySelector('[data-room-body]').scrollTop = 0;
            document.body.style.overflow = 'hidden';
        } catch {
            dialog?.remove();
            error.textContent = 'Room details could not be loaded. Please try again.';
            error.hidden = false;
        } finally {
            loading = false;
            button.disabled = false;
            button.removeAttribute('aria-busy');
            button.textContent = label;
        }
    });
});

// Delegation also handles booking buttons in details loaded after page startup.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-book-room]');
    if (!button) return;
    button.closest('dialog')?.close();
    window.openBooking(button.dataset.bookRoom);
});
