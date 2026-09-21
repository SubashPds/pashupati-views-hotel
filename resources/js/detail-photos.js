const viewer = document.getElementById('detail-photo-viewer');
let initializePhotos = () => {};

export function initializeDetailPhotos(root = document) {
    initializePhotos(root);
}

if (viewer) {
    const fullscreenImage = viewer.querySelector('[data-fullscreen-image]');
    const title = viewer.querySelector('#detail-photo-title');
    const error = viewer.querySelector('[data-photo-error]');
    const previous = viewer.querySelector('[data-photo-prev]');
    const next = viewer.querySelector('[data-photo-next]');
    let active = null;
    let previousOverflow = '';

    function renderFullscreen() {
        const photo = active.photos[active.index];
        error.hidden = true;
        fullscreenImage.src = photo.src;
        fullscreenImage.alt = photo.caption;
        title.textContent = `${photo.caption} · ${active.index + 1} / ${active.photos.length}`;
        previous.hidden = next.hidden = active.photos.length < 2;
    }

    function navigate(event, change) {
        if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        change(event.key === 'ArrowLeft' ? -1 : 1);
    }

    const initialized = new WeakSet();
    initializePhotos = (root) => root.querySelectorAll('[data-detail-photos]').forEach((container) => {
        if (initialized.has(container)) return;
        initialized.add(container);
        const kind = container.dataset.detailPhotos;
        const photo = container.querySelector(`[data-${kind}-photo]`);
        const caption = container.querySelector(`[data-${kind}-caption]`);
        const thumbnails = [...container.querySelectorAll(`[data-${kind}-thumbnail]`)];
        const state = {
            index: 0,
            photos: thumbnails.length
                ? thumbnails.map((button) => ({ src: button.dataset.photoSrc, caption: button.dataset.photoCaption }))
                : [{ src: photo.src, caption: photo.alt }],
            select(index) {
                state.index = (index + state.photos.length) % state.photos.length;
                const selected = state.photos[state.index];
                photo.src = selected.src;
                photo.alt = selected.caption;
                caption.textContent = `${selected.caption} · ${state.index + 1} / ${state.photos.length}`;
                thumbnails.forEach((button, i) => button.setAttribute('aria-pressed', String(i === state.index)));
                if (active === state && viewer.open) renderFullscreen();
            },
        };
        thumbnails.forEach((button, index) => button.addEventListener('click', () => state.select(index)));
        container.querySelector('[data-photo-prev]')?.addEventListener('click', () => state.select(state.index - 1));
        container.querySelector('[data-photo-next]')?.addEventListener('click', () => state.select(state.index + 1));
        container.addEventListener('keydown', (event) => navigate(event, (direction) => state.select(state.index + direction)));
        container.querySelector('[data-photo-expand]').addEventListener('click', () => {
            active = state;
            previousOverflow = document.body.style.overflow;
            renderFullscreen();
            viewer.showModal();
            document.body.style.overflow = 'hidden';
        });
    });
    initializeDetailPhotos();

    previous.addEventListener('click', () => active?.select(active.index - 1));
    next.addEventListener('click', () => active?.select(active.index + 1));
    viewer.addEventListener('keydown', (event) => navigate(event, (direction) => active?.select(active.index + direction)));
    viewer.querySelector('[data-photo-close]').addEventListener('click', () => viewer.close());
    fullscreenImage.addEventListener('error', () => { if (viewer.open) error.hidden = false; });
    viewer.addEventListener('close', () => {
        fullscreenImage.removeAttribute('src');
        active = null;
        document.body.style.overflow = previousOverflow;
    });
}
