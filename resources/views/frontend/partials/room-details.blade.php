@php
    $photos = collect();
    if ($room->cover_image_url) {
        $photos->push(['url' => $room->cover_image_url, 'caption' => $room->name]);
    }
    foreach ($room->images as $image) {
        $photos->push(['url' => Storage::url($image->image_path), 'caption' => $image->caption ?: $room->name]);
    }
    $photos = $photos->unique('url')->values();
    $description = html_entity_decode(strip_tags(preg_replace('/<\s*(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6])\s*>/i', "\n", $room->description ?: $room->short_description ?: '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp

<dialog id="room-details-{{ $room->id }}" class="room-details-dialog rounded-2xl border border-gold/20 bg-ivory p-0 text-navy shadow-2xl" aria-labelledby="room-title-{{ $room->id }}">
    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-navy/10 bg-white/80 px-4 py-3 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <h2 id="room-title-{{ $room->id }}" class="line-clamp-2 text-lg font-semibold leading-snug tracking-tight" title="{{ $room->name }}">{{ $room->name }}</h2>
            <span class="hidden shrink-0 rounded-full border border-gold/20 bg-gold/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-navy/70 sm:inline-flex">{{ ucfirst($room->category) }}</span>
        </div>
        <button type="button" data-close-room autofocus aria-label="Close room details" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-navy/50 transition-colors hover:bg-navy/5 hover:text-navy focus-visible:outline-2 focus-visible:outline-gold">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div data-room-body class="min-h-0 overflow-y-auto overscroll-contain space-y-6 p-4 sm:p-6">
        @include('frontend.partials.detail-photos', ['kind' => 'room'])

        @if($room->tagline)
        <p class="text-sm font-medium text-navy/70">{{ $room->tagline }}</p>
        @endif

        <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600">
            @if($room->size_sqm)<span>📐 {{ $room->size_sqm }} m²</span>@endif
            @if($room->max_guests)<span>👥 Up to {{ $room->max_guests }} guests</span>@endif
            @if($room->bed_type)<span>🛏 {{ $room->bed_type }}</span>@endif
        </div>

        @if(trim($description) !== '')
        <div>
            <h3 class="mb-2 font-semibold">About this room</h3>
            <p class="whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ trim($description) }}</p>
        </div>
        @endif

        @if(!empty($room->amenities) && is_array($room->amenities))
        <div>
            <h3 class="mb-3 font-semibold">Amenities</h3>
            <ul class="flex flex-wrap gap-2">
                @foreach($room->amenities as $amenity)
                <li class="rounded-lg border border-gold/20 bg-gold/5 px-3 py-2 text-sm">{{ $amenity }}</li>
                @endforeach
            </ul>
        </div>
        @endif

    </div>

    <div class="flex shrink-0 items-center justify-between gap-3 border-t border-navy/10 bg-white/90 px-4 py-3 sm:px-6">
        <div class="shrink-0">
            <p class="whitespace-nowrap text-lg font-semibold leading-tight tracking-tight"><span class="mr-1 text-[10px] font-medium tracking-wider text-navy/50">{{ $currency->code }}</span>{{ $currency->amount($room->price_per_night) }}</p>
            <p class="mt-0.5 text-[11px] leading-tight text-navy/50">per night</p>
        </div>
        <button type="button" data-book-room="{{ $room->name }}" aria-label="Book {{ $room->name }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-linear-to-br from-navy to-navy-lt px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:brightness-125 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold sm:px-5 sm:text-sm">
            Book room
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
        </button>
    </div>
</dialog>
