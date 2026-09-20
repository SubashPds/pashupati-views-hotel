const navigationBar = document.querySelector('[data-navigation-bar]');
const navigationLinks = [...document.querySelectorAll('#site-header .nav-link[href^="#"]')];

if (navigationBar && navigationLinks.length) {
    const sections = [...new Set(navigationLinks.map((link) => link.getAttribute('href')))]
        .map((href) => ({
            href,
            element: document.querySelector(href === '#home' ? '#hero-section' : href),
        }))
        .filter(({ element }) => element);
    let frame;
    let measureFrame;
    let positions = [];
    let navigationHeight = 0;
    let viewportHeight = window.innerHeight;
    let pageHeight = 0;
    let activeHref;

    function measureSections() {
        measureFrame = null;
        const scrollTop = window.scrollY;
        const height = navigationBar.getBoundingClientRect().height;
        positions = sections
            .filter(({ element }) => element.getClientRects().length)
            .map(({ href, element }) => ({ href, top: element.getBoundingClientRect().top + scrollTop }));
        viewportHeight = window.innerHeight;
        pageHeight = document.documentElement.scrollHeight;

        // Read layout together, then write only when the header actually resizes.
        if (height !== navigationHeight) {
            navigationHeight = height;
            document.documentElement.style.setProperty('--navigation-height', `${height}px`);
        }
        scheduleUpdate();
    }

    function scheduleMeasurement() {
        if (measureFrame == null) measureFrame = requestAnimationFrame(measureSections);
    }

    function updateActiveSection() {
        frame = null;
        if (!positions.length) return;

        // Scrolling uses cached document offsets; it never remeasures the page.
        const scrollTop = window.scrollY;
        const boundary = scrollTop + navigationHeight + 24;
        let active = positions[0];

        for (const section of positions) {
            if (section.top <= boundary) active = section;
        }

        if (scrollTop > 0 && scrollTop + viewportHeight >= pageHeight - 2) {
            active = positions[positions.length - 1];
        }

        if (active.href === activeHref) return;
        activeHref = active.href;
        navigationLinks.forEach((link) => {
            if (link.getAttribute('href') === active.href) {
                link.setAttribute('aria-current', 'location');
            } else if (link.hasAttribute('aria-current')) {
                link.removeAttribute('aria-current');
            }
        });
    }

    function scheduleUpdate() {
        if (frame == null) frame = requestAnimationFrame(updateActiveSection);
    }

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleMeasurement);
    window.addEventListener('hashchange', scheduleUpdate);
    window.addEventListener('pageshow', scheduleMeasurement);
    window.addEventListener('load', scheduleMeasurement);
    document.fonts.ready.then(scheduleMeasurement);
    const observer = new ResizeObserver(scheduleMeasurement);
    observer.observe(navigationBar);
    observer.observe(document.body);
    sections.forEach(({ element }) => observer.observe(element));
    scheduleMeasurement();
}
