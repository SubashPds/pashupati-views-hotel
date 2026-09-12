document.querySelectorAll('[data-blog-cover]').forEach((container) => {
    const input = container.querySelector('input[type="file"]');
    const preview = container.querySelector('[data-cover-preview]');
    const image = container.querySelector('[data-cover-image]');
    const clear = container.querySelector('[data-clear-cover]');
    const remove = container.querySelector('[name="remove_cover"]');
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
