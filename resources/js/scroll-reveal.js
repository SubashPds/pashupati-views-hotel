const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const targets = [...document.querySelectorAll('[data-scroll-reveal]')];

if (targets.length && !reducedMotion.matches && 'IntersectionObserver' in window && 'animate' in Element.prototype) {
    const animations = new Set();
    const observer = new IntersectionObserver((entries) => {
        let position = 0;
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            observer.unobserve(entry.target);
            if (reducedMotion.matches || entry.boundingClientRect.bottom <= 0) return;

            // Animate once, without hiding content or interfering with hover transforms.
            const animation = entry.target.animate([
                { opacity: 0.35, translate: '0 10px' },
                { opacity: 1, translate: '0 0' },
            ], {
                duration: 260,
                delay: Math.min(position++ * 30, 60),
                easing: 'cubic-bezier(0.2, 0.7, 0.3, 1)',
                fill: 'backwards',
            });
            animations.add(animation);
            animation.finished.then(() => animations.delete(animation), () => animations.delete(animation));
        });
    }, { threshold: 0, rootMargin: '0px 0px 24px 0px' });

    targets.forEach((target) => {
        // Keep the initial viewport and restored scroll position immediately readable.
        if (target.getBoundingClientRect().top >= window.innerHeight) observer.observe(target);
    });

    reducedMotion.addEventListener('change', (event) => {
        if (!event.matches) return;
        observer.disconnect();
        animations.forEach((animation) => animation.cancel());
        animations.clear();
    });
}
