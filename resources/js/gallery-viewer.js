const viewer = document.getElementById('gallery-viewer');

if (viewer) {
    const photo = viewer.querySelector('[data-gallery-image]');
    const video = viewer.querySelector('[data-gallery-video]');
    const title = viewer.querySelector('#gallery-viewer-title');
    const error = viewer.querySelector('[data-gallery-error]');
    const items = [...document.querySelectorAll('[data-gallery-open]')];
    const previous = viewer.querySelector('[data-gallery-prev]');
    const next = viewer.querySelector('[data-gallery-next]');
    let index = 0;
    let previousOverflow = '';
    previous.hidden = next.hidden = items.length < 2;

    function clearMedia() {
        photo.hidden = true;
        video.hidden = true;
        video.pause();
        video.removeAttribute('src');
        video.load();
        photo.removeAttribute('src');
        error.hidden = true;
    }

    function showMedia(nextIndex) {
        clearMedia();
        index = (nextIndex + items.length) % items.length;
        const button = items[index];
        title.textContent = button.dataset.mediaTitle;

        if (button.dataset.mediaType === 'video') {
            video.hidden = false;
            video.setAttribute('aria-label', button.dataset.mediaTitle);
            video.src = button.dataset.mediaSrc;
            video.play().catch(() => {});
        } else {
            photo.hidden = false;
            photo.alt = button.dataset.mediaTitle;
            photo.src = button.dataset.mediaSrc;
        }
    }

    items.forEach((button, itemIndex) => {
        button.addEventListener('click', () => {
            previousOverflow = document.body.style.overflow;
            viewer.showModal();
            document.body.style.overflow = 'hidden';
            showMedia(itemIndex);
        });
    });

    previous.addEventListener('click', () => showMedia(index - 1));
    next.addEventListener('click', () => showMedia(index + 1));
    viewer.addEventListener('keydown', (event) => {
        if (items.length < 2 || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
        // Keep native video seeking and volume controls available when the player has focus.
        if (event.target.closest('video, input, textarea, select, [contenteditable="true"]')) return;
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            showMedia(index + (event.key === 'ArrowLeft' ? -1 : 1));
        }
    });

    [photo, video].forEach((media) => {
        media.addEventListener('error', () => {
            if (viewer.open && !media.hidden) error.hidden = false;
        });
    });
    viewer.querySelector('[data-gallery-close]').addEventListener('click', () => viewer.close());
    viewer.addEventListener('click', (event) => {
        if (event.target !== viewer) return;
        const rect = viewer.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) viewer.close();
    });
    viewer.addEventListener('close', () => {
        clearMedia();
        if (!document.querySelector('dialog[open]')) document.body.style.overflow = previousOverflow;
    });
}
