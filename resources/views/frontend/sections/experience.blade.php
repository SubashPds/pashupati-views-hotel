{{--
  Section: Experience highlights
  Props: $experiences (Collection<Experience>), $settings
--}}
@if($experiences->isNotEmpty())
<section id="experience" style="padding:96px 0; background: linear-gradient(160deg,#0d1b2a 0%,#1a2d42 100%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-14">
            <span class="section-label">{{ $settings['experience_subtitle'] ?? 'THE EXPERIENCE' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold text-white mt-3">
                {{ $settings['experience_title'] ?? 'A quieter rhythm.' }}
            </h2>
        </div>

        {{-- Experience cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($experiences as $exp)
            <div class="group p-6 rounded-2xl border border-white/8 hover:border-amber-400/30 transition-all duration-300 card-lift"
                 style="background:rgba(255,255,255,0.04);">
                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">{{ $exp->icon ?? '✦' }}</div>
                <h3 class="font-semibold text-white text-base mb-2">{{ $exp->title }}</h3>
                @if($exp->description)
                <p class="text-sm text-gray-400 leading-relaxed">{{ $exp->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
