{{--
  Section: Hero
  Props (via $settings collection):
    hero_badge, hero_heading, hero_subheading, hero_body, hero_cta_primary
--}}

<section id="hero-section"
         class="relative min-h-screen flex flex-col items-center justify-center overflow-hidden"
         style="background: linear-gradient(160deg, #0d1b2a 0%, #1a2d42 40%, #0d1b2a 100%);">

    {{-- Ambient glow orbs --}}
    <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
        <div class="absolute -top-32 right-0 w-[500px] h-[500px] rounded-full opacity-10"
             style="background:radial-gradient(circle, #b8953b 0%, transparent 70%); filter:blur(60px);"></div>
        <div class="absolute bottom-0 -left-40 w-[600px] h-[600px] rounded-full opacity-8"
             style="background:radial-gradient(circle, #b8953b 0%, transparent 60%); filter:blur(80px);"></div>
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
            <span data-lang="en">{{ $settings['hero_badge'] }}</span>
            <span data-lang="ne" class="deva" style="display:none;">{{ $settings['hero_badge'] }}</span>
            <span data-lang="hi" class="deva" style="display:none;">{{ $settings['hero_badge'] }}</span>
        </div>
        @endif

        {{-- Main heading --}}
        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-[1.08] mb-5 text-white">
            <span data-lang="en">
                @if(!empty($settings['hero_heading']))
                    {!! nl2br(e($settings['hero_heading'])) !!}
                @else
                    Where comfort meets <span class="text-gold-gradient">devotion.</span>
                @endif
            </span>
            <span data-lang="ne" class="deva" style="display:none;">
                जहाँ आराम र <span class="text-gold-gradient">भक्ति</span> मिल्छन्।
            </span>
            <span data-lang="hi" class="deva" style="display:none;">
                जहाँ आराम और <span class="text-gold-gradient">भक्ति</span> मिलते हैं।
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
            <span data-lang="en">{{ $settings['hero_body'] }}</span>
            <span data-lang="ne" class="deva" style="display:none;">पशुपतिनाथ मन्दिरको छेउमा अवस्थित, एक सावधानीपूर्वक कल्पना गरिएको बसाइ।</span>
            <span data-lang="hi" class="deva" style="display:none;">पशुपतिनाथ मंदिर के निकट, एक सोच-समझकर तैयार किया गया प्रवास।</span>
        </p>
        @endif

        {{-- CTA --}}
        <div class="flex flex-wrap items-center justify-center gap-4">
            <button onclick="openBooking()"
                    class="inline-flex items-center gap-2 px-8 py-4 text-sm font-bold text-white rounded-xl shadow-xl transition-all hover:brightness-110 active:scale-95"
                    style="background:linear-gradient(135deg,#b8953b,#d4af5b); box-shadow:0 8px 32px rgba(184,149,59,0.30);">
                {{ $settings['hero_cta_primary'] ?? 'Plan your stay ↗' }}
            </button>
            <a href="#rooms"
               class="inline-flex items-center gap-2 px-8 py-4 text-sm font-semibold rounded-xl border transition-all hover:bg-white/5 active:scale-95"
               style="color:#d4af5b; border-color:rgba(184,149,59,0.30);">
                <span data-lang="en">Explore rooms ↓</span>
                <span data-lang="ne" class="deva" style="display:none;">कोठाहरू हेर्नुहोस् ↓</span>
                <span data-lang="hi" class="deva" style="display:none;">कमरे देखें ↓</span>
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
