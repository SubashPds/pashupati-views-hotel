<dialog id="detail-photo-viewer" aria-labelledby="detail-photo-title" class="border-0 bg-navy p-0 text-white">
    <div class="flex shrink-0 items-center justify-between gap-4 px-4 py-3 sm:px-6">
        <p id="detail-photo-title" class="min-w-0 text-sm" aria-live="polite"></p>
        <button type="button" data-photo-close autofocus aria-label="Close full-screen image" class="shrink-0 rounded-lg px-4 py-3 text-sm font-semibold hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-gold">Close ×</button>
    </div>
    <div class="relative min-h-0 flex-1">
        <img data-fullscreen-image alt="" class="absolute inset-0 h-full w-full object-contain">
        <p data-photo-error hidden role="status" class="absolute inset-x-0 top-1/2 px-16 text-center text-white">This image could not be loaded.</p>
        <button type="button" data-photo-prev aria-label="Previous photo" class="absolute left-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-navy/80 text-2xl text-white focus-visible:outline-2 focus-visible:outline-gold sm:left-6">‹</button>
        <button type="button" data-photo-next aria-label="Next photo" class="absolute right-2 top-1/2 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/20 bg-navy/80 text-2xl text-white focus-visible:outline-2 focus-visible:outline-gold sm:right-6">›</button>
    </div>
</dialog>
