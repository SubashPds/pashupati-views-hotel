{{--
  Section: Packages
  Props: $packages (Collection<Package>), $settings
--}}
@if($packages->isNotEmpty())
<section id="packages" class="py-24" style="background:linear-gradient(170deg,#0d1b2a 0%,#162435 60%,#0d1b2a 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-16">
            <span class="section-label" style="color:#d4af5b;">STAY EXPERIENCES</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-5xl font-bold mt-4 text-white">
                Curated Packages
            </h2>
            <p class="mt-4 text-base text-gray-400 max-w-xl mx-auto">
                Tailored experiences that go beyond a simple room — moments designed around your purpose of visit.
            </p>
        </div>

        {{-- Package Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($packages as $pkg)
            @php
                $gradients = [
                    'linear-gradient(135deg,#1a2d42 0%,#243d58 100%)',
                    'linear-gradient(135deg,#2a1f0d 0%,#3d2d10 100%)',
                    'linear-gradient(135deg,#1a1a2d 0%,#2d1a3d 100%)',
                    'linear-gradient(135deg,#1a2d1a 0%,#243d24 100%)',
                    'linear-gradient(135deg,#2d1a2a 0%,#3d2438 100%)',
                    'linear-gradient(135deg,#1a2a2d 0%,#24383d 100%)',
                ];
                $emojis = ['🛕','✈️','👨‍👩‍👧‍👦','💑','🏡','🚗'];
                $grad   = $gradients[$loop->index % count($gradients)];
                $emoji  = $emojis[$loop->index % count($emojis)];
                $isPopular = strtolower($pkg->badge ?? '') === 'most popular';
            @endphp

            <div class="group relative flex flex-col rounded-2xl overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl"
                 style="background:{{ $grad }}; border:1px solid rgba(212,175,91,0.12); box-shadow:0 4px 20px rgba(13,27,42,0.4);">

                {{-- Popular ribbon --}}
                @if($isPopular)
                <div class="absolute top-0 right-0 z-10">
                    <div class="bg-amber-500 text-white text-xs font-bold px-4 py-1.5 rounded-bl-xl rounded-tr-xl shadow-lg">
                        ⭐ MOST POPULAR
                    </div>
                </div>
                @endif

                {{-- Cover image or gradient hero --}}
                <div class="relative h-44 overflow-hidden shrink-0">
                    @if($pkg->cover_image_url)
                        <img src="{{ $pkg->cover_image_url }}"
                             alt="{{ $pkg->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 items-center justify-center flex-col gap-2 hidden"
                             style="background:{{ $grad }};">
                            <span class="text-6xl">{{ $emoji }}</span>
                        </div>
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center gap-3"
                             style="background:{{ $grad }};">
                            <span class="text-6xl group-hover:scale-110 transition-transform duration-300">{{ $emoji }}</span>
                        </div>
                    @endif

                    {{-- Dark gradient overlay --}}
                    <div class="absolute inset-0 pointer-events-none"
                         style="background:linear-gradient(to bottom, transparent 50%, rgba(13,27,42,0.8) 100%);"></div>

                    {{-- Tagline pill --}}
                    @if($pkg->tagline)
                    <div class="absolute bottom-3 left-4">
                        <span class="text-xs font-bold tracking-widest" style="color:#d4af5b;">
                            {{ strtoupper($pkg->tagline) }}
                        </span>
                    </div>
                    @endif

                    {{-- Badge (non-popular) --}}
                    @if($pkg->badge && !$isPopular)
                    <div class="absolute top-3 left-4">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full text-white"
                              style="background:rgba(13,27,42,0.75); backdrop-filter:blur(6px); border:1px solid rgba(212,175,91,0.3);">
                            {{ $pkg->badge }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="flex-1 flex flex-col p-6 gap-4">

                    {{-- Name + Meta --}}
                    <div>
                        <h3 class="text-lg font-bold text-white leading-snug">{{ $pkg->name }}</h3>
                        <div class="flex items-center gap-3 mt-2 flex-wrap">
                            @if($pkg->duration)
                            <span class="flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5 text-amber-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $pkg->duration }}
                            </span>
                            @endif
                            @if($pkg->min_guests)
                            <span class="flex items-center gap-1 text-xs text-gray-400">
                                <svg class="w-3.5 h-3.5 text-amber-500/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $pkg->min_guests }}{{ $pkg->max_guests ? '–'.$pkg->max_guests : '+' }} guests
                            </span>
                            @endif
                        </div>
                    </div>

                    {{-- Short description --}}
                    @if($pkg->short_description)
                    <p class="text-sm text-gray-400 leading-relaxed line-clamp-2">{{ $pkg->short_description }}</p>
                    @endif

                    {{-- Includes list (top 4) --}}
                    @if(!empty($pkg->includes))
                    <ul class="space-y-1.5">
                        @foreach(array_slice($pkg->includes, 0, 4) as $item)
                        <li class="flex items-start gap-2 text-xs text-gray-300">
                            <svg class="w-3.5 h-3.5 shrink-0 mt-0.5" style="color:#d4af5b;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $item }}
                        </li>
                        @endforeach
                        @if(count($pkg->includes) > 4)
                        <li class="text-xs text-gray-500 pl-5">+ {{ count($pkg->includes) - 4 }} more inclusions</li>
                        @endif
                    </ul>
                    @endif

                    {{-- Spacer --}}
                    <div class="flex-1"></div>

                    {{-- Price + CTA --}}
                    <div class="flex items-end justify-between gap-3 pt-4"
                         style="border-top:1px solid rgba(212,175,91,0.1);">
                        <div>
                            @if($pkg->price_label)
                            <p class="text-base font-bold" style="color:#d4af5b;">{{ $pkg->price_label }}</p>
                            @endif
                        </div>
                        <button type="button"
                                onclick="document.getElementById('enquiry-modal').classList.remove('hidden')"
                                class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200 hover:scale-105"
                                style="background:linear-gradient(135deg,#d4af5b,#b8953b); color:#0d1b2a;">
                            Enquire ↗
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bottom note --}}
        <p class="text-center text-xs text-gray-600 mt-10">
            All packages can be customised. Contact us to tailor a package that perfectly fits your itinerary.
        </p>
    </div>
</section>
@endif
