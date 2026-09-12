document.querySelectorAll('[data-offer-image]').forEach((container) => {
    const input = container.querySelector('input[type="file"]');
    const preview = container.querySelector('[data-offer-preview]');
    const image = container.querySelector('[data-offer-preview-image]');
    const clear = container.querySelector('[data-clear-offer-image]');
    const remove = container.querySelector('[name="remove_offer_image"]');
    const status = container.querySelector('[data-offer-image-status]');
    let objectUrl;

    function render() {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        const file = input.files[0];
        objectUrl = file ? URL.createObjectURL(file) : null;
        const source = objectUrl || (!remove?.checked ? container.dataset.savedUrl : '');
        if (source) image.src = source;
        else image.removeAttribute('src');
        preview.classList.toggle('hidden', !source);
        clear.classList.toggle('hidden', !file);
        status.textContent = file ? `${file.name} selected. Save settings to upload.` : '';
    }

    input.addEventListener('change', render);
    remove?.addEventListener('change', render);
    clear.addEventListener('click', () => {
        input.value = '';
        render();
        input.focus();
    });
    render();
});
