{{--
  Section: Gallery
  Props: $galleryItems (Collection<GalleryItem>), $settings
--}}
@if($galleryItems->isNotEmpty())
<section id="gallery" class="py-24" style="background:#f5f1ea;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <span class="section-label">{{ $settings['gallery_subtitle'] ?? 'A GLIMPSE INSIDE' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold mt-3" style="color:#0d1b2a;">
                {{ $settings['gallery_title'] ?? 'Spaces to unwind.' }}
            </h2>
        </div>

        {{-- Masonry-style grid via CSS columns --}}
        <div class="gallery-grid" style="columns:2; column-gap:1rem; orphans:1; widows:1;">
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

            @foreach($galleryItems as $item)
            @php
                $heights  = ['200px','260px','160px','220px','240px','180px'];
                $h        = $heights[$loop->index % count($heights)];
                $emoji    = $placeholderEmojis[$loop->index % count($placeholderEmojis)];
                $grad     = $placeholderGrads[$loop->index % count($placeholderGrads)];
                $hasImage = !empty($item->image_url);
            @endphp
            <div class="gallery-cell break-inside-avoid mb-4 rounded-xl overflow-hidden group cursor-pointer relative"
                 style="border:1px solid rgba(184,149,59,0.12); box-shadow:0 2px 8px rgba(13,27,42,0.08);">

                <div class="relative overflow-hidden" style="height:{{ $h }};">


                    @if($hasImage)
                        {{-- Real image --}}
                        <img src="{{ $item->image_url }}"
                             alt="{{ $item->title ?? 'Gallery photo' }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        {{-- Fallback shown if image errors --}}
                        <div class="absolute inset-0 items-center justify-center flex-col gap-2 hidden"
                             style="background:{{ $grad }};">
                            <span class="text-5xl">{{ $emoji }}</span>
                            @if($item->title)
                            <p class="text-xs text-amber-300/70 font-medium px-4 text-center">{{ $item->title }}</p>
                            @endif
                        </div>
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
                              style="background:rgba(13,27,42,0.75); backdrop-filter:blur(4px); border:1px solid rgba(184,149,59,0.25);">
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
            </div>

            @endforeach
        </div>

        @php $smColStart = 2; @endphp
        <style>
            @media(min-width:640px) { .gallery-grid { columns:3 !important; } }
            @media(min-width:1024px){ .gallery-grid { columns:4 !important; } }
        </style>
    </div>
</section>
@endif
