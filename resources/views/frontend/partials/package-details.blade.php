@php
    $photos = collect();
    if ($pkg->cover_image_url) {
        $photos->push(['url' => $pkg->cover_image_url, 'caption' => $pkg->name]);
    }
    foreach ($pkg->images as $image) {
        $photos->push(['url' => Storage::url($image->image_path), 'caption' => $image->caption ?: $pkg->name]);
    }
    $photos = $photos->unique('url')->values();
    $description = html_entity_decode(strip_tags(preg_replace('/<\s*(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6])\s*>/i', "\n", $pkg->description ?: $pkg->short_description ?: '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
@endphp

<dialog id="package-details-{{ $pkg->id }}" class="package-details-dialog rounded-2xl border border-gold/20 bg-ivory p-0 text-navy shadow-2xl" aria-labelledby="package-title-{{ $pkg->id }}">
    <div class="flex shrink-0 items-center justify-between gap-3 border-b border-navy/10 bg-white/80 px-4 py-3 sm:px-6">
        <div class="flex min-w-0 items-center gap-3">
            <h2 id="package-title-{{ $pkg->id }}" class="line-clamp-2 text-lg font-semibold leading-snug tracking-tight" title="{{ $pkg->name }}">{{ $pkg->name }}</h2>
            <span class="hidden shrink-0 rounded-full border border-gold/20 bg-gold/10 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider text-navy/70 sm:inline-flex">Package</span>
        </div>
        <button type="button" data-close-package autofocus aria-label="Close package details" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-navy/50 transition-colors hover:bg-navy/5 hover:text-navy focus-visible:outline-2 focus-visible:outline-gold">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
    </div>

    <div data-package-body class="min-h-0 overflow-y-auto overscroll-contain space-y-6 p-4 sm:p-6">
        @include('frontend.partials.detail-photos', ['kind' => 'package'])

        @if($pkg->badge || $pkg->tagline)
        <div class="flex flex-wrap items-center gap-3">
            @if($pkg->badge)<span class="rounded-full border border-gold/20 bg-gold/10 px-3 py-1 text-xs font-semibold">{{ $pkg->badge }}</span>@endif
            @if($pkg->tagline)<p class="text-sm font-medium text-navy/70">{{ $pkg->tagline }}</p>@endif
        </div>
        @endif

        @if($pkg->duration || $pkg->min_guests || $pkg->max_guests)
        <dl class="flex flex-wrap gap-x-8 gap-y-3 text-sm">
            @if($pkg->duration)
            <div><dt class="mb-1 text-xs text-gray-500">Duration</dt><dd class="font-medium">{{ $pkg->duration }}</dd></div>
            @endif
            @if($pkg->min_guests || $pkg->max_guests)
            <div><dt class="mb-1 text-xs text-gray-500">Guests</dt><dd class="font-medium">
                @if($pkg->min_guests)
                    {{ $pkg->min_guests }}{{ $pkg->max_guests ? '–' . $pkg->max_guests : '+' }} guests
                @else
                    Up to {{ $pkg->max_guests }} guests
                @endif
            </dd></div>
            @endif
        </dl>
        @endif

        @if(trim($description) !== '')
        <div>
            <h3 class="mb-2 font-semibold">About this package</h3>
            <p class="whitespace-pre-line text-sm leading-relaxed text-gray-600">{{ trim($description) }}</p>
        </div>
        @endif

        @if(!empty($pkg->includes))
        <div>
            <h3 class="mb-3 font-semibold">What's included</h3>
            <ul class="grid gap-3 sm:grid-cols-2">
                @foreach($pkg->includes as $item)
                <li class="flex items-start gap-2 text-sm text-gray-600"><span class="text-gold" aria-hidden="true">✓</span>{{ $item }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(!empty($pkg->highlights))
        <div>
            <h3 class="mb-3 font-semibold">Highlights</h3>
            <ul class="flex flex-wrap gap-2">
                @foreach($pkg->highlights as $highlight)
                <li class="rounded-lg border border-gold/20 bg-gold/5 px-3 py-2 text-sm">{{ $highlight }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>

    <div class="flex shrink-0 items-center justify-between gap-3 border-t border-navy/10 bg-white/90 px-4 py-3 sm:px-6">
        <p data-currency-price="package-{{ $pkg->id }}" class="min-w-0 break-words text-sm font-semibold leading-snug tracking-tight sm:text-base">{{ $currency->package($pkg) }}</p>
        <a href="#contact" data-package-enquiry="{{ $pkg->name }}" data-package-id="{{ $pkg->id }}" aria-label="Enquire about {{ $pkg->name }}" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-linear-to-br from-navy to-navy-lt px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition-all hover:brightness-125 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold sm:px-5 sm:text-sm">
            Enquire
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-5-5 5 5-5 5"/></svg>
        </a>
    </div>
</dialog>
