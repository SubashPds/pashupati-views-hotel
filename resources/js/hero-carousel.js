const carousel = document.querySelector('[data-hero-carousel]');

if (carousel) {
    const slides = [...carousel.querySelectorAll('[data-slide]')];
    const dots = [...carousel.querySelectorAll('[data-dot]')];
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let index = 0;
    let paused = reducedMotion.matches;
    let inView = !('IntersectionObserver' in window);
    let timer;

    function canPlay() {
        return inView && !paused && !document.hidden;
    }

    function sync() {
        clearTimeout(timer);
        const activeVideo = slides[index]?.querySelector('video');
        if (activeVideo?.ended && canPlay() && slides.length > 1) {
            show(index + 1);
            return;
        }
        slides.forEach((slide, i) => {
            slide.hidden = i !== index;
            const video = slide.querySelector('video');
            if (video) {
                if (i === index && canPlay()) {
                    video.muted = true;
                    video.play().catch(() => {});
                } else {
                    video.pause();
                }
            }
        });
        dots.forEach((dot, i) => {
            dot.setAttribute('aria-current', String(i === index));
            dot.style.backgroundColor = i === index ? '#b8953b' : 'rgba(0,0,0,.5)';
        });
        if (!activeVideo && canPlay() && slides.length > 1) {
            timer = setTimeout(() => show(index + 1), 8000);
        }
    }

    function show(next) {
        const nextIndex = (next + slides.length) % slides.length;
        if (nextIndex !== index) {
            const video = slides[nextIndex].querySelector('video');
            if (video) video.currentTime = 0;
        }
        index = nextIndex;
        sync();
    }

    dots.forEach((dot, i) => dot.addEventListener('click', () => show(i)));
    slides.forEach((slide, i) => {
        slide.querySelector('video')?.addEventListener('ended', () => {
            if (i === index && canPlay() && slides.length > 1) {
                show(index + 1);
            }
        });
    });
    document.addEventListener('visibilitychange', sync);
    reducedMotion.addEventListener('change', () => { paused = reducedMotion.matches; sync(); });
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            if (inView === entry.isIntersecting) return;
            inView = entry.isIntersecting;
            sync();
        });
        observer.observe(carousel);
    }
    sync();
}
