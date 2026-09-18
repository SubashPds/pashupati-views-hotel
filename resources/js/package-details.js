document.querySelectorAll('[data-package-details]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.packageDetails);
        if (!dialog) return;
        dialog.dataset.previousOverflow = document.body.style.overflow;
        dialog.showModal();
        dialog.querySelector('[data-package-body]').scrollTop = 0;
        document.body.style.overflow = 'hidden';
    });
});

document.querySelectorAll('.package-details-dialog').forEach((dialog) => {
    dialog.querySelector('[data-close-package]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', (event) => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) {
            dialog.close();
        }
    });
    dialog.addEventListener('close', () => {
        if (!document.querySelector('dialog[open]')) {
            document.body.style.overflow = dialog.dataset.previousOverflow || '';
        }
    });
    dialog.querySelectorAll('[data-package-thumbnail]').forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            const photo = dialog.querySelector('[data-package-photo]');
            photo.src = thumbnail.dataset.photoSrc;
            photo.alt = thumbnail.dataset.photoCaption;
            dialog.querySelector('[data-package-caption]').textContent = thumbnail.dataset.photoCaption;
            dialog.querySelectorAll('[data-package-thumbnail]').forEach((button) => {
                button.setAttribute('aria-pressed', String(button === thumbnail));
            });
        });
    });
});

document.querySelectorAll('[data-package-enquiry]').forEach((link) => {
    link.addEventListener('click', () => {
        link.closest('dialog')?.close();
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
});
