{{--
  Section: Hero
  Props (via $settings collection):
    hero_badge, hero_heading, hero_subheading, hero_body, hero_cta_primary
--}}

<section id="hero-section"
         class="relative flex flex-col items-center justify-center overflow-hidden pt-12 pb-24"
         style="background: linear-gradient(160deg, #0d1b2a 0%, #1a2d42 40%, #0d1b2a 100%);">

    @if($heroSlides->isNotEmpty())
    <div class="absolute inset-0" data-hero-carousel role="region" aria-roledescription="carousel" aria-label="Hotel highlights">
        @foreach($heroSlides as $slide)
        <div data-slide class="absolute inset-0" @if(!$loop->first) hidden @endif role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} of {{ $heroSlides->count() }}: {{ $slide->title }}">
            @if($slide->media_type === 'video')
            <video class="w-full h-full object-cover" muted playsinline @if($heroSlides->count() === 1) loop @endif preload="{{ $loop->first ? 'metadata' : 'none' }}" aria-label="{{ $slide->title }}">
                <source src="{{ $slide->media_url }}" type="{{ str_ends_with($slide->media_path, '.webm') ? 'video/webm' : 'video/mp4' }}">
            </video>
            @else
            <img src="{{ $slide->media_url }}" alt="{{ $slide->title }}" class="w-full h-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async">
            @endif
        </div>
        @endforeach
        <div class="absolute inset-0 pointer-events-none" style="background:rgba(5,15,25,.65)"></div>
        <div class="absolute bottom-16 inset-x-0 z-20 flex flex-wrap justify-center items-center gap-3 px-4 text-white">
            @if($heroSlides->count() > 1)
            @foreach($heroSlides as $slide)
            <button type="button" data-dot="{{ $loop->index }}" aria-label="Show slide {{ $loop->iteration }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" class="h-2 w-2 rounded-full bg-black/50 transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white" style="background-color:{{ $loop->first ? '#b8953b' : 'rgba(0,0,0,.5)' }}"></button>
            @endforeach
            @endif
        </div>
    </div>
    @endif

    {{-- Ambient glow orbs --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 right-0 w-[500px] h-[500px] rounded-full opacity-10"
             style="background:radial-gradient(circle, #b8953b 0%, transparent 70%);"></div>
        <div class="absolute bottom-0 -left-40 w-[600px] h-[600px] rounded-full opacity-8"
             style="background:radial-gradient(circle, #b8953b 0%, transparent 60%);"></div>
        {{-- Subtle grid texture --}}
        <div class="absolute inset-0 opacity-3"
             style="background-image: repeating-linear-gradient(0deg, rgba(184,149,59,0.06) 0px, transparent 1px), repeating-linear-gradient(90deg, rgba(184,149,59,0.06) 0px, transparent 1px); background-size:72px 72px;"></div>
    </div>

    <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 text-center">

        {{-- Badge --}}
        @if(!empty($settings['hero_badge']))
        <div class="inline-flex items-center gap-2 mb-6 px-4 py-2 rounded-full text-xs font-bold uppercase tracking-widest border"
             style="color:#d4af5b; background:rgba(184,149,59,0.08); border-color:rgba(184,149,59,0.20);">
            <span aria-hidden="true">✦</span>
            <span>{{ $settings['hero_badge'] }}</span>
        </div>
        @endif

        {{-- Main heading --}}
        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.08] mb-5 text-white">
            <span>
                @if(!empty($settings['hero_heading']))
                    {!! nl2br(e($settings['hero_heading'])) !!}
                @else
                    Stay beside the sacred <span class="text-gold-gradient">Pashupatinath Temple.</span>
                @endif
            </span>
        </h1>

        {{-- Sub-heading --}}
        @if(!empty($settings['hero_subheading']))
        <p class="deva text-lg md:text-xl font-medium mb-3" style="color:#d4af5b; opacity:0.85;">
            {{ $settings['hero_subheading'] }}
        </p>
        @endif

        {{-- Body text --}}
        @if(!empty($settings['hero_body']))
        <p class="text-base sm:text-lg text-gray-400 max-w-2xl mx-auto mb-10 leading-relaxed">
            <span>{{ $settings['hero_body'] }}</span>
        </p>
        @endif

        {{-- CTA --}}
        <div class="flex flex-wrap items-center justify-center gap-4">
            <button data-open-booking
                    class="inline-flex items-center gap-2 px-8 py-4 text-sm font-bold text-white rounded-xl shadow-xl transition-all hover:brightness-110 active:scale-95"
                    style="background:linear-gradient(135deg,#b8953b,#d4af5b); box-shadow:0 8px 32px rgba(184,149,59,0.30);">
                {{ $settings['hero_cta_primary'] ?? 'Book your stay ↗' }}
            </button>
            <a href="#rooms"
               class="inline-flex items-center gap-2 px-8 py-4 text-sm font-semibold rounded-xl border transition-all hover:bg-white/5 active:scale-95"
               style="color:#d4af5b; border-color:rgba(184,149,59,0.30);">
                <span>Explore rooms ↓</span>
            </a>
        </div>

        {{-- Trust signals --}}
        <div class="flex flex-wrap items-center justify-center gap-5 mt-12 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">✓ Free cancellation enquiries</span>
            <span class="hidden sm:flex items-center gap-1.5">·</span>
            <span class="flex items-center gap-1.5">✓ 24/7 guest support</span>
            <span class="hidden sm:flex items-center gap-1.5">·</span>
            <span class="flex items-center gap-1.5">✓ Best rate guarantee</span>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <a href="#rooms"
       class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-gray-500 hover:text-amber-400 transition-colors"
       aria-label="Scroll to rooms">
        <svg class="w-5 h-5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/>
        </svg>
    </a>
</section>
