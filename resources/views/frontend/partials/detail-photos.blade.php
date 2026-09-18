@if($photos->isNotEmpty())
<div data-detail-photos="{{ $kind }}">
    <figure>
        <div class="relative">
            <button type="button" data-photo-expand aria-label="View image full screen" aria-haspopup="dialog" aria-controls="detail-photo-viewer" class="block w-full rounded-xl focus-visible:outline-2 focus-visible:outline-gold">
                <img data-{{ $kind }}-photo src="{{ $photos[0]['url'] }}" alt="{{ $photos[0]['caption'] }}" class="aspect-[4/3] max-h-96 w-full rounded-xl bg-navy/5 object-contain" loading="lazy">
                <span class="absolute bottom-3 right-3 rounded-lg bg-navy/80 px-3 py-2 text-xs font-semibold text-white">Full screen ↗</span>
            </button>
            @if($photos->count() > 1)
            <button type="button" data-photo-prev aria-label="Previous photo" class="absolute left-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-navy/80 text-xl text-white focus-visible:outline-2 focus-visible:outline-gold">‹</button>
            <button type="button" data-photo-next aria-label="Next photo" class="absolute right-2 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-navy/80 text-xl text-white focus-visible:outline-2 focus-visible:outline-gold">›</button>
            @endif
        </div>
        <figcaption data-{{ $kind }}-caption class="mt-2 text-sm text-gray-500" aria-live="polite">{{ $photos[0]['caption'] }}</figcaption>
    </figure>
    @if($photos->count() > 1)
    <div data-photo-thumbnails class="mt-3 flex gap-3 overflow-x-auto p-1" aria-label="{{ ucfirst($kind) }} photos">
        @foreach($photos as $photo)
        <button type="button" data-{{ $kind }}-thumbnail data-photo-src="{{ $photo['url'] }}" data-photo-caption="{{ $photo['caption'] }}"
                aria-label="Show photo {{ $loop->iteration }}: {{ $photo['caption'] }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                class="shrink-0 overflow-hidden rounded-lg border-2 border-transparent aria-pressed:border-gold focus-visible:outline-2 focus-visible:outline-gold">
            <img src="{{ $photo['url'] }}" alt="" class="h-16 w-24 object-cover" loading="lazy">
        </button>
        @endforeach
    </div>
    @endif
</div>
@else
<div class="rounded-xl bg-navy/5 p-10 text-center text-gray-500">{{ ucfirst($kind) }} photos coming soon.</div>
@endif
