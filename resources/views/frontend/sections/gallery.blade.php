{{--
  Section: Gallery
  Props: $galleryItems (Collection<GalleryItem>), $settings
--}}
@if($galleryItems->isNotEmpty())
<section id="gallery" data-home-gallery class="py-10 sm:py-12" style="background:#f5f1ea;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div data-scroll-reveal class="text-center mb-8 sm:mb-10">
            <span class="section-label">{{ $settings['gallery_subtitle'] ?? 'A GLIMPSE INSIDE' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold mt-3" style="color:#0d1b2a;">
                {{ $settings['gallery_title'] ?? 'Spaces to unwind.' }}
            </h2>
        </div>

        @php
            $visibleItems = isset($limit) ? $galleryItems->take($limit) : $galleryItems;
            $galleryCategories = $visibleItems->map(fn ($item) => strtolower(trim($item->section ?? '')) ?: 'general')->unique()->values();
        @endphp
        <div data-gallery-filters hidden role="group" aria-label="Filter gallery by category" class="mb-8 text-center">
            <button type="button" data-gallery-filter="" aria-pressed="true" aria-controls="gallery-grid">All</button>
            @foreach($galleryCategories as $category)
            <button type="button" data-gallery-filter="{{ $category }}" aria-pressed="false" aria-controls="gallery-grid">{{ \Illuminate\Support\Str::headline($category) }}</button>
            @endforeach
        </div>
        <p data-gallery-count class="sr-only" role="status" aria-live="polite" aria-atomic="true"></p>

        {{-- Masonry-style grid via CSS columns --}}
        <div id="gallery-grid" class="gallery-grid" style="columns:2; column-gap:1rem; orphans:1; widows:1;">
            @php
                $placeholderEmojis = ['🛏️','🌄','🌿','🍽️','🏛️','🌅','🛕','🏔️','🌸','🎋'];
                $placeholderGrads  = [
                    'linear-gradient(135deg,#1a2d42,#243d58)',
                    'linear-gradient(135deg,#243d58,#0d1b2a)',
                    'linear-gradient(135deg,#2a1f0d,#3d2d10)',
                    'linear-gradient(135deg,#1a2d42,#2d3a1a)',
                    'linear-gradient(135deg,#1a1a2d,#2d1a2d)',
                ];
            @endphp

            @foreach($visibleItems as $item)
            @php
                $heights  = ['200px','260px','160px','220px','240px','180px'];
                $h        = $heights[$loop->index % count($heights)];
                $emoji    = $placeholderEmojis[$loop->index % count($placeholderEmojis)];
                $grad     = $placeholderGrads[$loop->index % count($placeholderGrads)];
                $hasImage = !empty($item->image_url);
            @endphp
            <div class="gallery-cell break-inside-avoid mb-4 rounded-xl overflow-hidden group relative"
                 data-gallery-category="{{ strtolower(trim($item->section ?? '')) ?: 'general' }}"
                 style="border:1px solid rgba(184,149,59,0.12); box-shadow:0 2px 8px rgba(13,27,42,0.08);">

                <div class="relative overflow-hidden" style="height:{{ $h }};">


                    @if($hasImage && $item->media_type === 'video')
                        {{-- The original video is requested only when opened in the viewer. --}}
                        <div class="absolute inset-0 flex flex-col gap-3 items-center justify-center pointer-events-none" style="background:{{ $grad }};" aria-hidden="true">
                            <span class="flex h-12 w-12 items-center justify-center rounded-full border border-white/40 bg-navy/60 text-white">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor"><path d="m9 5 11 7-11 7z"/></svg>
                            </span>
                            <span class="text-xs font-medium text-white/80">Play video</span>
                        </div>
                    @elseif($hasImage)
                        {{-- Real image --}}
                        <img data-home-gallery-src="{{ $item->preview_url }}" data-gallery-preview hidden
                             alt="{{ $item->title ?? 'Gallery photo' }}"
                             class="w-full h-full object-cover"
                             decoding="async" fetchpriority="low">
                        {{-- Reserve the tile while the preview loads, or if it fails. --}}
                        <div data-gallery-fallback class="absolute inset-0 flex items-center justify-center flex-col gap-2"
                             style="background:{{ $grad }};">
                            <span class="text-5xl">{{ $emoji }}</span>
                            @if($item->title)
                            <p class="text-xs text-amber-300/70 font-medium px-4 text-center">{{ $item->title }}</p>
                            @endif
                        </div>
                        <noscript><img src="{{ $item->preview_url }}" alt="{{ $item->title ?? 'Gallery photo' }}" loading="lazy" decoding="async" class="absolute inset-0 w-full h-full object-cover"></noscript>
                    @else
                        {{-- Placeholder tile when no image uploaded --}}
                        <div class="w-full h-full flex flex-col items-center justify-center gap-3 group-hover:brightness-110 transition-all"
                             style="background:{{ $grad }};">
                            <span class="text-6xl group-hover:scale-110 transition-transform duration-300">{{ $emoji }}</span>
                            @if($item->title)
                            <p class="text-xs font-medium px-6 text-center" style="color:rgba(212,175,91,0.8);">{{ $item->title }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Gradient overlay on hover --}}
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
                         style="background:linear-gradient(to top, rgba(13,27,42,0.6) 0%, transparent 60%);"></div>

                    {{-- Badge --}}
                    @if($item->badge_label)
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-lg text-white"
                              style="background:rgba(13,27,42,0.9); border:1px solid rgba(184,149,59,0.25);">
                            {{ $item->badge_label }}
                        </span>
                    </div>
                    @endif

                    {{-- Title caption (fade in on hover for real images) --}}
                    @if($hasImage && $item->title)
                    <div class="absolute bottom-0 inset-x-0 p-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                         style="background:linear-gradient(to top, rgba(13,27,42,0.85) 0%, transparent 100%);">
                        <p class="text-xs font-medium text-white/90">{{ $item->title }}</p>
                    </div>
                    @endif
                </div>
                @if($hasImage)
                <button type="button" data-gallery-open data-media-src="{{ $item->image_url }}" data-media-type="{{ $item->media_type }}" data-media-title="{{ $item->title ?: ($item->media_type === 'video' ? 'Gallery video' : 'Gallery photo') }}"
                        aria-haspopup="dialog" aria-controls="gallery-viewer" aria-label="Enlarge {{ $item->title ?: ($item->media_type === 'video' ? 'gallery video' : 'gallery photo') }}"
                        class="absolute inset-0 z-10 cursor-pointer rounded-xl focus-visible:outline-2 focus-visible:-outline-offset-4 focus-visible:outline-gold">
                    <span class="absolute right-3 top-3 flex h-8 w-8 items-center justify-center rounded-full bg-navy/60 text-white transition-colors group-hover:bg-navy/90" aria-hidden="true">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/></svg>
                    </span>
                </button>
                @endif
            </div>

            @endforeach
        </div>

        @if(isset($limit) && $galleryItems->count() > $limit)
        <div class="mt-10 text-center">
            <a href="{{ route('gallery') }}" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 text-sm font-semibold rounded-xl text-white transition-all shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-gold focus:ring-offset-2" style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                View Full Gallery
            </a>
        </div>
        @endif

        <style>
            [data-gallery-filters] button { margin:0.25rem; padding:0.6rem 1.1rem; border:1px solid rgba(133,101,52,0.3); border-radius:999px; color:#856534; font-size:0.875rem; font-weight:600; cursor:pointer; }
            [data-gallery-filters] button:hover { background:#ebe3d5; }
            [data-gallery-filters] button[aria-pressed="true"] { background:#0d1b2a; color:#fff; border-color:#0d1b2a; }
            [data-gallery-filters] button:focus-visible { outline:2px solid #856534; outline-offset:3px; }
            .gallery-cell[hidden] { display:none; }
            @media(min-width:640px) { .gallery-grid { columns:3 !important; } }
            @media(min-width:1024px){ .gallery-grid { columns:4 !important; } }
        </style>
    </div>
</section>

@include('frontend.partials.gallery-viewer')
@endif
