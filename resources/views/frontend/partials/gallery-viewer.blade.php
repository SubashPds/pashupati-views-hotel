<dialog id="gallery-viewer" class="rounded-2xl border border-white/10 bg-navy p-0 text-white shadow-2xl" aria-labelledby="gallery-viewer-title">
    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-white/10 px-4 py-2 sm:px-6">
        <div class="min-w-0 py-2">
            <p data-gallery-position class="mb-1 text-[10px] font-semibold uppercase tracking-widest text-gold-light"></p>
            <h2 id="gallery-viewer-title" class="line-clamp-2 text-sm font-medium" aria-live="polite"></h2>
        </div>
        <button type="button" data-gallery-close autofocus aria-label="Close gallery viewer" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white/70 transition-colors hover:bg-white/10 hover:text-white focus-visible:outline-2 focus-visible:outline-gold">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <div class="relative min-h-0 flex-1 bg-black/30">
        <img data-gallery-image hidden alt="" class="absolute inset-0 h-full w-full object-contain">
        <video data-gallery-video hidden controls playsinline preload="metadata" class="absolute inset-0 h-full w-full object-contain"></video>
        <p data-gallery-error hidden role="status" class="absolute inset-0 m-auto h-fit px-6 text-center text-sm text-white/70">This media could not be loaded. Please try again.</p>
        <button type="button" data-gallery-prev hidden aria-label="Previous gallery item" class="absolute left-2 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-navy/70 text-white shadow-lg backdrop-blur-sm transition-colors hover:bg-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold sm:left-4">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg>
        </button>
        <button type="button" data-gallery-next hidden aria-label="Next gallery item" class="absolute right-2 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-navy/70 text-white shadow-lg backdrop-blur-sm transition-colors hover:bg-navy focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold sm:right-4">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg>
        </button>
    </div>
</dialog>
