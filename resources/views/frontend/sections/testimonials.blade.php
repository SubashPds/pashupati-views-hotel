{{--
  Section: Testimonials
  Props: $testimonials (Collection<Testimonial>), $settings
--}}
@if($testimonials->isNotEmpty())
<section id="testimonials" class="py-10 sm:py-12 overflow-hidden bg-ivory">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div data-scroll-reveal class="text-center mb-8 sm:mb-10">
            <span class="section-label" style="color:#856534;">{{ $settings['testimonials_subtitle'] ?? 'MOMENTS TO REMEMBER' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold text-navy mt-3">
                {{ $settings['testimonials_title'] ?? 'A few words about the stay.' }}
            </h2>
        </div>

        {{-- Slider --}}
        <div class="relative">
            <div id="testimonial-track"
                 class="flex gap-6 overflow-x-auto snap-scroll pb-4 scrollbar-hide"
                 style="-ms-overflow-style:none; scrollbar-width:none;">

                @foreach($testimonials as $t)
                <div data-scroll-reveal class="snap-item flex-shrink-0 w-full sm:w-[calc(50%-12px)] lg:w-[calc(33.333%-16px)]
                            p-7 rounded-2xl border border-gold/15 flex flex-col"
                     style="background:#fff; box-shadow:0 8px 24px rgba(13,27,42,0.04);">

                    {{-- Stars --}}
                    <div class="stars text-lg mb-4" style="color:#856534;" aria-label="{{ $t->rating }} out of 5 stars">
                        {{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}
                    </div>

                    {{-- Quote --}}
                    <div class="text-4xl mb-2" style="color:rgba(184,149,59,0.25); font-family:Georgia,serif;">"</div>
                    <blockquote class="text-gray-600 text-sm leading-relaxed flex-1 -mt-6 mb-5">
                        {{ $t->review }}
                    </blockquote>

                    {{-- Author --}}
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gold/15">
                        <div>
                            <p class="text-navy text-sm font-semibold">{{ $t->author_name }}</p>
                            @if($t->author_date)
                            <p class="text-xs text-gray-500 mt-0.5">{{ $t->author_date }}</p>
                            @endif
                        </div>
                        @if($t->tag)
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                              style="background:rgba(184,149,59,0.10); color:#856534; border:1px solid rgba(184,149,59,0.20);">
                            {{ $t->tag }}
                        </span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Dot navigation (shows on mobile where scrollbar hidden) --}}
            @if($testimonials->count() > 1)
            <div id="testimonial-dots" class="flex items-center justify-center gap-2 mt-6" role="tablist" aria-label="Testimonial navigation">
                @foreach($testimonials as $i => $t)
                <button type="button"
                        class="dot w-2 h-2 rounded-full transition-all duration-300"
                        style="{{ $i === 0 ? 'background:#856534; width:1.5rem;' : 'background:rgba(133,101,52,0.22);' }}"
                        data-index="{{ $i }}" aria-label="Testimonial {{ $i + 1 }}" role="tab">
                </button>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(function() {
    const track = document.getElementById('testimonial-track');
    const dots  = document.querySelectorAll('#testimonial-dots .dot');
    if (!track || !dots.length) return;

    let current = 0;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    function scrollTo(idx) {
        const items = track.querySelectorAll('.snap-item');
        if (!items[idx]) return;

        // Scroll only the track horizontally to avoid jumping the whole page viewport
        track.scrollTo({
            left: items[idx].offsetLeft - track.offsetLeft,
            behavior: reducedMotion.matches ? 'instant' : 'smooth'
        });

        dots.forEach((d, i) => {
            d.style.background   = i === idx ? '#856534' : 'rgba(133,101,52,0.22)';
            d.style.width        = i === idx ? '1.5rem' : '0.5rem';
            d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
        });
        current = idx;
    }

    dots.forEach((d, i) => d.addEventListener('click', () => scrollTo(i)));

    // Offscreen sliders should not keep doing animation and layout work.
    let timer;
    let inView = !('IntersectionObserver' in window);
    let interacted = false;
    function syncAutoplay() {
        clearInterval(timer);
        if (inView && !interacted && !document.hidden && !reducedMotion.matches) {
            timer = setInterval(() => scrollTo((current + 1) % dots.length), 6000);
        }
    }
    function stopAutoplay() {
        interacted = true;
        syncAutoplay();
    }
    track.addEventListener('pointerdown', stopAutoplay);
    document.getElementById('testimonial-dots')?.addEventListener('click', stopAutoplay);
    document.addEventListener('visibilitychange', syncAutoplay);
    reducedMotion.addEventListener('change', syncAutoplay);
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            inView = entry.isIntersecting;
            syncAutoplay();
        });
        observer.observe(track);
    }
    syncAutoplay();

    // Sync dots on manual scroll
    let scrollTimer;
    track.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(() => {
            const items = track.querySelectorAll('.snap-item');
            let closest = 0, minDist = Infinity;
            items.forEach((el, i) => {
                const dist = Math.abs(el.getBoundingClientRect().left - track.getBoundingClientRect().left);
                if (dist < minDist) { minDist = dist; closest = i; }
            });
            if (closest !== current) scrollTo(closest);
        }, 100);
    }, { passive: true });
})();
</script>
@endpush
@endif
