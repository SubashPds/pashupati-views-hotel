document.querySelectorAll('[data-faq-section]').forEach((section) => {
    const search = section.querySelector('[data-faq-search]');
    if (!search) return;

    const items = [...section.querySelectorAll('[data-faq-item]')];
    const count = section.querySelector('[data-faq-count]');
    const clear = section.querySelector('[data-faq-clear]');
    const expand = section.querySelector('[data-faq-expand]');
    const empty = section.querySelector('[data-faq-no-results]');
    const searchableItems = items.map((item) => ({
        item,
        text: `${item.querySelector('[data-faq-question]').textContent} ${item.querySelector('[data-faq-answer]').textContent}`.toLocaleLowerCase(),
    }));
    let savedOpenItems = null;

    section.querySelector('[data-faq-search-tools]').hidden = false;
    expand.hidden = false;

    function updateExpand() {
        const visible = items.filter((item) => !item.hidden);
        const allOpen = visible.length > 0 && visible.every((item) => item.open);
        expand.hidden = visible.length === 0;
        expand.replaceChildren(document.createTextNode(allOpen ? 'Collapse all ' : 'Expand all '));
        const icon = document.createElement('span');
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = allOpen ? '−' : '+';
        expand.append(icon);
    }

    function filterItems() {
        const query = search.value.trim().toLocaleLowerCase();
        if (query && savedOpenItems === null) savedOpenItems = new Set(items.filter((item) => item.open));

        searchableItems.forEach(({ item, text }) => {
            item.hidden = query !== '' && !text.includes(query);
            if (query) {
                // Show answers with matches, including matches found only in answer text.
                item.open = !item.hidden;
            } else if (savedOpenItems !== null) {
                item.open = savedOpenItems.has(item);
            }
        });
        if (!query) savedOpenItems = null;

        const visibleCount = items.filter((item) => !item.hidden).length;
        count.textContent = query
            ? `${visibleCount} ${visibleCount === 1 ? 'answer found' : 'answers found'}`
            : `${items.length} ${items.length === 1 ? 'question' : 'questions'} to help you plan`;
        empty.hidden = visibleCount !== 0;
        clear.hidden = search.value.length === 0;
        updateExpand();
    }

    function resetSearch() {
        search.value = '';
        filterItems();
        search.focus();
    }

    items.forEach((item) => item.addEventListener('toggle', updateExpand));
    search.addEventListener('input', filterItems);
    clear.addEventListener('click', resetSearch);
    section.querySelector('[data-faq-reset]').addEventListener('click', resetSearch);
    expand.addEventListener('click', () => {
        const visible = items.filter((item) => !item.hidden);
        const shouldOpen = !visible.every((item) => item.open);
        visible.forEach((item) => { item.open = shouldOpen; });
        updateExpand();
    });
    updateExpand();
});
