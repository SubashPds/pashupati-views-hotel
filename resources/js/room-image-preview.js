document.querySelectorAll('[data-room-image-upload]').forEach((upload) => {
    const input = upload.querySelector('input[type="file"]');
    const preview = upload.querySelector('[data-upload-preview]');
    const images = upload.querySelector('[data-preview-images]');
    const savedCover = upload.querySelector('[data-saved-cover]');
    const status = upload.querySelector('[data-upload-status]');
    let selectedFiles = [...input.files];
    let urls = [];

    function syncFiles() {
        const transfer = new DataTransfer();
        selectedFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    }

    function renderPreview() {
        urls.forEach((url) => URL.revokeObjectURL(url));
        urls = [];
        images.replaceChildren();
        const files = selectedFiles;
        preview.hidden = files.length === 0;
        if (savedCover) savedCover.hidden = files.length > 0;
        status.textContent = files.length ? `${files.length} ${files.length === 1 ? 'image' : 'images'} selected. Save the room to upload.` : '';

        files.forEach((file, index) => {
            const figure = document.createElement('figure');
            figure.className = 'relative min-w-0';
            const image = document.createElement('img');
            const url = URL.createObjectURL(file);
            urls.push(url);
            image.src = url;
            image.alt = `Preview of ${file.name}`;
            image.className = 'w-full h-36 object-contain rounded-xl bg-black/20';
            const caption = document.createElement('figcaption');
            caption.className = 'mt-1.5 break-all text-xs text-gray-400';
            caption.textContent = file.name;
            image.addEventListener('error', () => {
                image.hidden = true;
                caption.textContent = `${file.name} — preview unavailable`;
            });
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.dataset.removePreview = '';
            remove.className = 'absolute top-2 right-2 flex h-8 w-8 items-center justify-center rounded-full bg-gray-950/80 text-lg text-white shadow-sm transition-colors hover:bg-red-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-400';
            remove.textContent = '×';
            remove.setAttribute('aria-label', `Remove ${file.name}`);
            remove.addEventListener('click', () => {
                selectedFiles = selectedFiles.filter((_, selectedIndex) => selectedIndex !== index);
                syncFiles();
                renderPreview();
                status.textContent = `${file.name} removed. ${input.files.length} images selected.`;
                const buttons = images.querySelectorAll('[data-remove-preview]');
                buttons[Math.min(index, buttons.length - 1)]?.focus();
            });
            figure.append(image, remove, caption);
            images.append(figure);
        });
    }

    input.addEventListener('change', () => {
        const incoming = [...input.files];
        if (input.multiple) {
            incoming.forEach((file) => {
                const alreadySelected = selectedFiles.some((selected) =>
                    selected.name === file.name && selected.size === file.size &&
                    selected.type === file.type && selected.lastModified === file.lastModified);
                if (!alreadySelected) selectedFiles.push(file);
            });
        } else {
            selectedFiles = incoming;
        }
        syncFiles();
        renderPreview();
    });
    upload.querySelector('[data-clear-upload]').addEventListener('click', () => {
        selectedFiles = [];
        input.value = '';
        renderPreview();
    });
    input.form?.addEventListener('reset', () => setTimeout(() => {
        selectedFiles = [...input.files];
        renderPreview();
    }, 0));
    renderPreview();
});
