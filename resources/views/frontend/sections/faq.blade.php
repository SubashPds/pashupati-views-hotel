{{--
  Section: FAQ
  Props: $faqs (Collection<Faq>), $settings
--}}
@if($faqs->isNotEmpty())
<section id="faq" class="py-10 sm:py-14" style="background:#faf8f3;">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Section Header --}}
        <div data-scroll-reveal class="text-center mb-10 sm:mb-12">
            <span class="section-label" style="color:#856534;">NEED ANSWERS?</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold text-navy mt-3">
                Frequently Asked Questions
            </h2>
            <p class="text-gray-500 text-sm mt-3 max-w-xl mx-auto leading-relaxed">
                Everything you need to know about your stay at Pashupati Views Hotel.
                Can't find what you're looking for? <a href="#contact" class="font-medium" style="color:#856534;">Contact us directly.</a>
            </p>
        </div>

        {{-- Accordion Items --}}
        <div data-scroll-reveal class="space-y-3" role="list">
            @foreach($faqs as $faq)
            @php $faqId = 'faq-' . $faq->id; @endphp
            <div class="faq-item rounded-2xl border transition-all duration-200"
                 style="background:#fff; border-color:rgba(184,149,59,0.15); box-shadow:0 2px 16px rgba(13,27,42,0.04);"
                 role="listitem">

                {{-- Question (trigger) --}}
                <button type="button"
                        id="{{ $faqId }}-btn"
                        aria-expanded="false"
                        aria-controls="{{ $faqId }}-panel"
                        onclick="toggleFaq('{{ $faqId }}')"
                        class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left group">
                    <span class="text-navy font-semibold text-sm sm:text-base leading-snug group-hover:text-amber-800 transition-colors">
                        {{ $faq->question }}
                    </span>
                    <span id="{{ $faqId }}-icon"
                          class="faq-icon shrink-0 w-7 h-7 flex items-center justify-center rounded-full transition-all duration-300"
                          style="background:rgba(184,149,59,0.10); color:#856534;">
                        <svg class="w-4 h-4 transition-transform duration-300" fill="none"
                             stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>
                </button>

                {{-- Answer (panel) --}}
                <div id="{{ $faqId }}-panel"
                     role="region"
                     aria-labelledby="{{ $faqId }}-btn"
                     class="faq-panel hidden px-6 pb-5">
                    <div class="h-px mb-4" style="background:rgba(184,149,59,0.12);"></div>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {!! nl2br(e($faq->answer)) !!}
                    </p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Bottom CTA --}}
        <div data-scroll-reveal class="mt-12 text-center py-8 px-6 rounded-2xl border"
             style="background:rgba(184,149,59,0.04); border-color:rgba(184,149,59,0.18);">
            <p class="text-navy font-semibold text-base mb-1">Still have questions?</p>
            <p class="text-gray-500 text-sm mb-4">Our team is ready to help you plan the perfect stay.</p>
            <div class="flex flex-wrap items-center justify-center gap-3">
                <a href="#contact"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-all hover:brightness-110"
                   style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                    Send Us a Message ↗
                </a>
                @if(!empty($settings['contact_whatsapp']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition-all hover:brightness-110"
                   style="background:#25d366;">
                    WhatsApp ↗
                </a>
                @endif
            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
function toggleFaq(id) {
    const panel  = document.getElementById(id + '-panel');
    const btn    = document.getElementById(id + '-btn');
    const icon   = document.getElementById(id + '-icon');
    const svg    = icon?.querySelector('svg');
    const isOpen = !panel.classList.contains('hidden');

    // Close all open panels first
    document.querySelectorAll('.faq-panel').forEach(p => {
        if (!p.classList.contains('hidden')) {
            p.classList.add('hidden');
            const b  = document.getElementById(p.id.replace('-panel', '-btn'));
            const ic = document.getElementById(p.id.replace('-panel', '-icon'));
            if (b)  b.setAttribute('aria-expanded', 'false');
            if (ic) {
                ic.style.background = 'rgba(184,149,59,0.10)';
                const s = ic.querySelector('svg');
                if (s) s.style.transform = 'rotate(0deg)';
            }
        }
    });

    // Toggle the clicked one open
    if (!isOpen) {
        panel.classList.remove('hidden');
        btn.setAttribute('aria-expanded', 'true');
        icon.style.background = 'rgba(184,149,59,0.20)';
        if (svg) svg.style.transform = 'rotate(180deg)';
    }
}
</script>
@endpush
@endif
