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

    function updateActiveSection() {
        frame = null;
        const visibleSections = sections.filter(({ element }) => element.getClientRects().length);
        if (!visibleSections.length) return;

        const bar = navigationBar.getBoundingClientRect();
        document.documentElement.style.setProperty('--navigation-height', `${bar.height}px`);
        const boundary = Math.max(bar.bottom, bar.height) + 24;
        let active = visibleSections[0];

        for (const section of visibleSections) {
            if (section.element.getBoundingClientRect().top <= boundary) active = section;
        }

        if (window.scrollY > 0 && window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2) {
            active = visibleSections[visibleSections.length - 1];
        }

        navigationLinks.forEach((link) => {
            if (link.getAttribute('href') === active.href) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }

    function scheduleUpdate() {
        if (frame == null) frame = requestAnimationFrame(updateActiveSection);
    }

    window.addEventListener('scroll', scheduleUpdate, { passive: true });
    window.addEventListener('resize', scheduleUpdate);
    window.addEventListener('hashchange', scheduleUpdate);
    window.addEventListener('pageshow', scheduleUpdate);
    const observer = new ResizeObserver(scheduleUpdate);
    observer.observe(navigationBar);
    sections.forEach(({ element }) => observer.observe(element));
    scheduleUpdate();
}
