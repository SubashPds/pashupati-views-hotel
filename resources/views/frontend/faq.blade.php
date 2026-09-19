@extends('layouts.app')
@section('title', 'FAQs — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('meta_description', 'Answers to frequently asked questions about staying at Pashupati Views Hotel.')

@section('content')

{{-- ── FAQ header — matches the gallery page ────────────────────────── --}}
<div class="faq-hero relative overflow-hidden bg-ivory">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav aria-label="Breadcrumb" class="gallery-breadcrumb">
            <a href="{{ route('home') }}">Home</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">FAQ</span>
        </nav>

        <div class="gallery-collection-heading mt-10 sm:mt-12">
            <div>
                <p class="section-label">Good to know</p>
                <h1 class="faq-title mt-2.5 text-2xl sm:text-3xl font-medium tracking-tight text-navy">Frequently Asked Questions</h1>
            </div>
            <p class="gallery-collection-hint">
                Find answers to common questions about your stay, booking, and experiences at our hotel.
            </p>
        </div>

        <div class="mt-8 pt-6 border-t border-gold/20 flex justify-center">
            <label for="faq-search" class="sr-only">Search FAQs</label>
            <div class="relative w-full max-w-2xl">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400" aria-hidden="true">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z"/></svg>
                </span>
                <input id="faq-search" type="search" placeholder="Search questions..."
                       class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-200"/>
            </div>
        </div>
    </div>
</div>

{{-- ── FAQ Accordion ─────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-b from-[#faf8f3] to-white py-16">
    <div class="max-w-4xl mx-auto px-6 sm:px-10 lg:px-8">

        @if($faqs->isEmpty())
        <div class="py-28 text-center">
            <p class="text-gray-300 text-xs uppercase tracking-widest mb-3">No FAQs yet</p>
            <p class="text-gray-500 text-sm">We're working on it.
                <a href="#contact" class="underline underline-offset-2 font-medium text-[#b8953b]">Contact us</a> in the meantime.</p>
        </div>

        @else
        <div role="list" class="faq-grid space-y-4" id="faq-list">

            @foreach($faqs as $index => $faq)
            @php $faqId = 'faq-' . $faq->id; @endphp

            <div id="{{ $faqId }}"
                 class="faq-item group relative rounded-xl border shadow-sm backdrop-blur-sm overflow-hidden
                        transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgba(184,149,59,0.15)]
                        animate-[faqFadeUp_0.5s_cubic-bezier(0.22,1,0.36,1)_both]
                        border-[rgba(184,149,59,0.15)] bg-white/60 hover:border-[rgba(184,149,59,0.3)] hover:bg-white/90"
                 style="animation-delay: {{ min($index * 0.05, 0.45) }}s"
                 role="listitem">

                <button type="button"
                        id="{{ $faqId }}-btn"
                        aria-expanded="false"
                        aria-controls="{{ $faqId }}-panel"
                        onclick="toggleFaq('{{ $faqId }}')"
                        class="faq-trigger w-full flex items-center justify-between gap-5 px-6 py-5 sm:py-6 text-left transition-all duration-200 hover:pl-8">

                    <div class="flex-1 min-w-0 pr-4">
                        <span class="faq-question block font-semibold text-base sm:text-lg leading-snug text-gray-900 transition-colors duration-200 group-hover:text-amber-700">
                            {{ $faq->question }}
                        </span>
                    </div>

                    <div class="faq-icon shrink-0 w-7 h-7 rounded-full flex items-center justify-center transition-colors duration-300 bg-transparent">
                        <svg id="{{ $faqId }}-chevron" class="w-4 h-4 text-gray-500 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>

                {{-- Answer --}}
                <div id="{{ $faqId }}-panel"
                     role="region"
                     aria-labelledby="{{ $faqId }}-btn"
                     class="faq-panel overflow-hidden transition-all duration-300 ease-in-out opacity-0"
                     style="display:none; max-height:0px;">
                    <div class="px-6 pb-6 pt-2 sm:pb-8">
                        <div class="faq-answer-inner text-gray-700 text-sm sm:text-base leading-relaxed">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

        </div>
        @endif

    </div>
</div>

{{-- ── CTA Section ──────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-b from-[#faf8f3] to-[#f5f3ed] py-16">
    <div class="max-w-4xl mx-auto px-6 sm:px-10 lg:px-8">
        <div class="faq-cta rounded-2xl overflow-hidden flex flex-col sm:flex-row items-center gap-8 px-8 py-12 sm:py-14 bg-white border border-[rgba(184,149,59,0.15)] shadow-[0_4px_20px_rgba(184,149,59,0.08)]">

            <div class="flex-1 text-center sm:text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] mb-2 text-[#b8953b]">Need More Help?</p>
                <p class="text-gray-900 font-bold text-2xl sm:text-3xl tracking-tight">We're Always Here</p>
                <p class="text-gray-600 text-sm mt-2">Our team responds within 24 hours to assist you.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                <a href="#contact"
                   class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl transition-all duration-200 hover:shadow-lg hover:scale-105 bg-gradient-to-br from-[#d4af5b] to-[#b8953b] text-[#0d1b2a]">
                    Send Message
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                @if(!empty($settings['contact_whatsapp']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                   target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl border transition-all duration-200 hover:shadow-md border-[rgba(184,149,59,0.3)] text-[#0d1b2a]"
                   aria-label="WhatsApp">
                    WhatsApp
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-4-4l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
/* Only keyframes remain here — Tailwind's arbitrary-value utilities (bg-[...], border-[...],
   shadow-[...], animate-[...], etc.) handle everything else, so no other custom CSS rules
   are needed. Keyframes still have to live in real CSS since Tailwind can't generate them
   from a utility class alone. */
@keyframes faqFloat {
    0%, 100% { transform: translateY(0px); }
    50%      { transform: translateY(20px); }
}
@keyframes faqFadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    /* ── Accordion open/close ─────────────────────────────────────── */
    function openItem(card) {
        const panel   = card.querySelector('[id$="-panel"]');
        const btn     = card.querySelector('button');
        const chevron = card.querySelector('svg[id$="-chevron"]');
        const icon    = card.querySelector('.faq-icon');

        card.classList.add('is-open', 'border-[rgba(184,149,59,0.4)]', 'bg-white/95', 'shadow-[0_8px_24px_rgba(184,149,59,0.2)]');
        card.classList.remove('border-[rgba(184,149,59,0.15)]', 'bg-white/60');

        btn.setAttribute('aria-expanded', 'true');
        chevron.classList.add('rotate-180');
        icon.classList.add('bg-[rgba(184,149,59,0.15)]');
        icon.classList.remove('bg-transparent');

        panel.style.display = 'block';
        panel.style.maxHeight = panel.scrollHeight + 'px';
        panel.classList.remove('opacity-0');
        panel.classList.add('opacity-100');

        // Once the transition finishes, release the fixed height so the
        // panel can still resize naturally (e.g. on window resize).
        window.setTimeout(() => {
            if (card.classList.contains('is-open')) {
                panel.style.maxHeight = 'none';
            }
        }, 320);
    }

    function closeItem(card) {
        const panel   = card.querySelector('[id$="-panel"]');
        const btn     = card.querySelector('button');
        const chevron = card.querySelector('svg[id$="-chevron"]');
        const icon    = card.querySelector('.faq-icon');

        panel.style.maxHeight = panel.scrollHeight + 'px';
        // eslint-disable-next-line no-unused-expressions
        panel.offsetHeight; // force reflow so the transition runs from the real height
        panel.style.maxHeight = '0px';
        panel.classList.remove('opacity-100');
        panel.classList.add('opacity-0');

        card.classList.remove('is-open', 'border-[rgba(184,149,59,0.4)]', 'bg-white/95', 'shadow-[0_8px_24px_rgba(184,149,59,0.2)]');
        card.classList.add('border-[rgba(184,149,59,0.15)]', 'bg-white/60');

        btn.setAttribute('aria-expanded', 'false');
        chevron.classList.remove('rotate-180');
        icon.classList.remove('bg-[rgba(184,149,59,0.15)]');
        icon.classList.add('bg-transparent');

        window.setTimeout(() => {
            if (!card.classList.contains('is-open')) {
                panel.style.display = 'none';
            }
        }, 320);
    }

    function toggleFaq(id) {
        const card = document.getElementById(id);
        if (!card) return;
        const isOpen = card.classList.contains('is-open');

        document.querySelectorAll('.faq-item.is-open').forEach((otherCard) => {
            if (otherCard !== card) closeItem(otherCard);
        });

        isOpen ? closeItem(card) : openItem(card);
    }
    window.toggleFaq = toggleFaq;

    /* ── Search ──────────────────────────────── */
    const searchInput = document.getElementById('faq-search');
    const activeClasses = ['is-active', 'bg-[rgba(184,149,59,0.08)]', 'border-[rgba(184,149,59,0.3)]', 'text-[#b8953b]'];

    function applyFilters() {
        const q      = (searchInput?.value || '').trim().toLowerCase();

        document.querySelectorAll('.faq-item').forEach((item) => {
            const question = (item.querySelector('.faq-question')?.textContent || '').toLowerCase();
            const answer   = (item.querySelector('.faq-answer-inner')?.textContent || '').toLowerCase();

            const matchesQuery = q === '' || question.includes(q) || answer.includes(q);

            item.classList.toggle('hidden', !matchesQuery);

            if (!matchesQuery && item.classList.contains('is-open')) {
                closeItem(item);
            }
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
})();
</script>
@endpush