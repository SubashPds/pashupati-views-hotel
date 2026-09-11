const hero = document.getElementById('hero-section');

if (hero) {
    const topBars = [
        document.getElementById('site-header'),
        document.querySelector('.announcement-bar'),
    ].filter(Boolean);

    function syncHeroHeight() {
        const height = topBars.reduce((total, bar) => total + bar.getBoundingClientRect().height, 0);
        hero.style.setProperty('--hero-top-height', `${height}px`);
    }

    const observer = new ResizeObserver(syncHeroHeight);
    topBars.forEach((bar) => observer.observe(bar));
    syncHeroHeight();
}
