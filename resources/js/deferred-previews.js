const jobs = new WeakMap();
const pending = new Set();
let loading = 0;

const observer = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
        const job = jobs.get(entry.target);
        if (!job || job.loading) return;
        if (entry.isIntersecting) pending.add(job);
        else pending.delete(job);
    });
    loadNext();
}, { rootMargin: '240px 0px', threshold: 0 }) : null;

// One shared limit prevents room, package, and gallery previews competing at section edges.
function loadNext() {
    while (loading < 2 && pending.size) {
        const job = pending.values().next().value;
        pending.delete(job);
        job.loading = true;
        loading++;
        observer.unobserve(job.image.parentElement);
        load(job, () => {
            loading--;
            loadNext();
        });
    }
}

function load({ image, sourceAttribute, fallback }, complete = () => {}) {
    let finished = false;
    const finish = () => {
        if (finished) return;
        finished = true;
        image.removeEventListener('load', onLoad);
        image.removeEventListener('error', onError);
        image.removeAttribute(sourceAttribute);
        complete();
    };
    const onLoad = async () => {
        try { await image.decode(); } catch { /* Keep the placeholder if decoding fails. */ }
        if (image.naturalWidth) {
            image.hidden = false;
            if (fallback) fallback.hidden = true;
        }
        finish();
    };
    const onError = () => {
        image.hidden = true;
        if (fallback) fallback.hidden = false;
        finish();
    };
    image.addEventListener('load', onLoad, { once: true });
    image.addEventListener('error', onError, { once: true });
    image.src = image.getAttribute(sourceAttribute);
}

export function deferPreviews(images, sourceAttribute, fallbackSelector) {
    images.forEach(image => {
        const job = { image, sourceAttribute, fallback: image.parentElement.querySelector(fallbackSelector) };
        if (observer) {
            jobs.set(image.parentElement, job);
            observer.observe(image.parentElement);
        } else {
            // Native lazy loading needs the image to occupy its reserved space.
            image.loading = 'lazy';
            image.hidden = false;
            load(job);
        }
    });
}
