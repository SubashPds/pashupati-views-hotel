{{--
  Section: Experience highlights
  Props: $experiences (Collection<Experience>), $settings
--}}
@if($experiences->isNotEmpty())
<section id="experience" class="py-10 sm:py-12" style="background:#fffdf9;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div data-scroll-reveal class="text-center mb-8 sm:mb-10">
            <span class="section-label" style="color:#856534;">{{ $settings['experience_subtitle'] ?? 'THE EXPERIENCE' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold text-navy mt-3">
                {{ $settings['experience_title'] ?? 'A quieter rhythm.' }}
            </h2>
        </div>

        {{-- Experience cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($experiences as $exp)
            <div data-scroll-reveal class="group p-6 rounded-2xl border border-gold/15 hover:border-gold/30 hover:shadow-lg transition-all duration-300"
                 style="background:#f7f4ed;">
                <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-300">{{ $exp->icon ?? '✦' }}</div>
                <h3 class="font-semibold text-navy text-base mb-2">{{ $exp->title }}</h3>
                @if($exp->description)
                <p class="text-sm text-gray-600 leading-relaxed">{{ $exp->description }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
