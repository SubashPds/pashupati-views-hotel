<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>@yield('title', 'Pashupati Views Hotel — Where Comfort Meets Devotion')</title>
    <meta name="description" content="@yield('meta_description', 'A premium hotel experience beside the sacred Pashupatinath Temple, Kathmandu, Nepal. Book your stay and discover a quieter rhythm.')">
    <meta property="og:title"       content="@yield('title', 'Pashupati Views Hotel')">
    <meta property="og:description" content="@yield('meta_description', 'A premium hotel experience in Kathmandu, Nepal.')">
    <meta property="og:type"        content="website">
    <meta property="og:locale"      content="en_US">

    {{-- Preload important fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>

<body class="antialiased text-gray-900 bg-[#faf8f3]" style="font-family:'Inter',sans-serif">

    {{-- ═══ ANNOUNCEMENT BANNER ═══ --}}
    @if(isset($settings) && ($settings['announcement_bar_active'] ?? false) && !empty($settings['announcement_bar']))
    <div class="announcement-bar text-center px-4 py-2.5 text-xs text-amber-300/90 font-medium tracking-widest">
        {{ $settings['announcement_bar'] }}
    </div>
    @endif

    {{-- ═══ NAVIGATION ═══ --}}
    <header id="site-header"
            class="sticky top-0 z-50 transition-all duration-300"
            style="background: rgba(13,27,42,0.97); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(184,149,59,0.15);">

        <div data-navigation-bar class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-18">

                {{-- Logo --}}
                <a href="{{ route('home') }}" class="flex items-center gap-3 group shrink-0" aria-label="Pashupati Views Hotel – Home">
                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xl"
                         style="background: linear-gradient(135deg,#b8953b,#d4af5b);">
                        🏨
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-bold text-white tracking-tight group-hover:text-amber-300 transition-colors">
                            {{ $settings['site_name'] ?? 'Pashupati Views Hotel' }}
                        </div>
                        <div class="text-xs font-medium tracking-widest uppercase" style="color:#b8953b;">Nepal</div>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden xl:flex items-center gap-1 text-sm font-medium" aria-label="Main navigation">
                    @foreach([
                        ['#home',         'Home'],
                        ['#rooms',        'Rooms'],
                        ['#packages',     'Packages'],
                        ['#experience',   'Experience'],
                        ['#services',     'Services'],
                        ['#gallery',      'Gallery'],
                        [route('blogs.index'),        'Blogs'],
                        ['#contact',      'Contact'],
                    ] as [$href, $label])
                    <a href="{{ str_starts_with($href, '#') && !request()->routeIs('home') ? route('home') . $href : $href }}"
                       @if($href === route('blogs.index') && request()->routeIs('blogs.*')) aria-current="location" @endif
                       class="nav-link px-3 py-2 rounded-lg text-gray-300 hover:text-amber-300 hover:bg-white/5 transition-all">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                {{-- Right actions --}}
                <div class="flex items-center gap-2">
                    {{-- Language switcher --}}
                    <div class="hidden sm:flex items-center gap-0.5 border border-white/10 rounded-lg overflow-hidden text-xs font-medium">
                        <button onclick="setLang('en')" id="lang-en"
                                class="lang-btn px-2.5 py-1.5 text-amber-300 bg-white/10 transition-colors" aria-label="English">EN</button>
                        <button onclick="setLang('ne')" id="lang-ne"
                                class="lang-btn px-2.5 py-1.5 text-gray-400 hover:text-amber-300 transition-colors" aria-label="Nepali">ने</button>
                        <button onclick="setLang('hi')" id="lang-hi"
                                class="lang-btn px-2.5 py-1.5 text-gray-400 hover:text-amber-300 transition-colors" aria-label="Hindi">हि</button>
                    </div>

                    {{-- Book Now CTA --}}
                    <button onclick="openBooking()"
                            class="hidden md:inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-all hover:brightness-110 shadow-md"
                            style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                        Book Now ↗
                    </button>

                    {{-- Hamburger --}}
                    <button id="nav-toggle"
                            class="xl:hidden p-2 rounded-lg hover:bg-white/10 transition-colors text-gray-300"
                            aria-expanded="false" aria-label="Toggle navigation">
                        <svg id="icon-menu"  class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Nav Drawer --}}
        <div id="mobile-nav"
             class="hidden xl:hidden border-t"
             style="border-color:rgba(184,149,59,0.15); background:rgba(13,27,42,0.98);">
            <div class="px-4 py-4 flex flex-col gap-1 text-sm font-medium">
                @foreach([
                    ['#home',       'Home'],
                    ['#rooms',      'Rooms'],
                    ['#packages',   'Packages'],
                    ['#experience', 'Experience'],
                    ['#services',   'Services'],
                    ['#gallery',    'Gallery'],
                    [route('blogs.index'),      'Blogs'],
                    ['#contact',    'Contact'],
                ] as [$href, $label])
                <a href="{{ str_starts_with($href, '#') && !request()->routeIs('home') ? route('home') . $href : $href }}"
                       @if($href === route('blogs.index') && request()->routeIs('blogs.*')) aria-current="location" @endif
                   class="nav-link px-4 py-3 rounded-xl text-gray-300 hover:text-amber-300 hover:bg-white/5 transition-all"
                   onclick="closeMobileNav()">
                    {{ $label }}
                </a>
                @endforeach

                {{-- Mobile language --}}
                <div class="flex gap-2 mt-2 pt-3 border-t" style="border-color:rgba(184,149,59,0.15);">
                    <button onclick="setLang('en')" class="lang-btn flex-1 py-2 text-xs font-semibold text-center rounded-lg text-amber-300 bg-white/10">EN</button>
                    <button onclick="setLang('ne')" class="lang-btn flex-1 py-2 text-xs font-semibold text-center rounded-lg text-gray-400 hover:text-amber-300 hover:bg-white/5 transition-colors">नेपाली</button>
                    <button onclick="setLang('hi')" class="lang-btn flex-1 py-2 text-xs font-semibold text-center rounded-lg text-gray-400 hover:text-amber-300 hover:bg-white/5 transition-colors">हिन्दी</button>
                </div>

                <button onclick="openBooking(); closeMobileNav()"
                        class="mt-2 py-3 px-6 text-sm font-bold text-center text-white rounded-xl transition-all"
                        style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                    Book Your Stay ↗
                </button>
            </div>
        </div>
    </header>

    {{-- ═══ PAGE CONTENT ═══ --}}
    <main id="home">
        @yield('content')
    </main>

    {{-- ═══ FOOTER ═══ --}}
    <footer style="background:#0d1b2a; border-top:1px solid rgba(184,149,59,0.15);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

                {{-- Brand --}}
                <div class="lg:col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-5 group">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xl"
                             style="background:linear-gradient(135deg,#b8953b,#d4af5b);">🏨</div>
                        <div>
                            <div class="text-white font-bold text-base leading-tight">{{ $settings['site_name'] ?? 'Pashupati Views Hotel' }}</div>
                            <div class="text-xs font-medium uppercase tracking-widest" style="color:#b8953b;">Nepal</div>
                        </div>
                    </a>
                    <p class="text-sm text-gray-400 leading-relaxed max-w-xs mb-5">
                        {{ $settings['site_tagline'] ?? 'Where comfort meets devotion.' }}
                        Nestled beside the sacred Pashupatinath Temple in the heart of Kathmandu.
                    </p>
                    {{-- Social / contact quick links --}}
                    <div class="flex items-center gap-3">
                        @if(!empty($settings['contact_whatsapp']))
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                           target="_blank" rel="noopener"
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white transition-all hover:brightness-110"
                           style="background:#25d366;" aria-label="Chat on WhatsApp">
                            WhatsApp ↗
                        </a>
                        @endif
                        @if(!empty($settings['contact_phone']))
                        <a href="tel:{{ $settings['contact_phone'] }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-white bg-white/10 hover:bg-white/15 transition-all"
                           aria-label="Call us">
                            Call Us ↗
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Quick Links --}}
                <div>
                    <h4 class="text-white font-semibold text-xs uppercase tracking-widest mb-4" style="color:#b8953b;">Explore</h4>
                    <ul class="space-y-2.5 text-sm text-gray-400">
                        @foreach([['#rooms','Rooms & Suites'],['#experience','Experiences'],['#services','Services'],['#gallery','Gallery'],[route('blogs.index'),'Blogs'],['#contact','Contact']] as [$h,$l])
                        <li><a href="{{ str_starts_with($h, '#') && !request()->routeIs('home') ? route('home') . $h : $h }}" class="hover:text-amber-300 transition-colors">{{ $l }}</a></li>
                        @endforeach
                    </ul>
                </div>

                {{-- Contact Details --}}
                <div>
                    <h4 class="text-white font-semibold text-xs uppercase tracking-widest mb-4" style="color:#b8953b;">Contact</h4>
                    <ul class="space-y-3 text-sm text-gray-400">
                        @if(!empty($settings['contact_address']))
                        <li class="flex items-start gap-2">
                            <span class="mt-0.5 shrink-0">📍</span>
                            <span>{{ $settings['contact_address'] }}</span>
                        </li>
                        @endif
                        @if(!empty($settings['contact_phone']))
                        <li class="flex items-center gap-2">
                            <span>📞</span>
                            <a href="tel:{{ $settings['contact_phone'] }}" class="hover:text-amber-300 transition-colors">{{ $settings['contact_phone'] }}</a>
                        </li>
                        @endif
                        @if(!empty($settings['contact_email']))
                        <li class="flex items-center gap-2">
                            <span>✉️</span>
                            <a href="mailto:{{ $settings['contact_email'] }}" class="hover:text-amber-300 transition-colors">{{ $settings['contact_email'] }}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="border-t mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600"
                 style="border-color:rgba(184,149,59,0.10);">
                <p>© {{ date('Y') }} {{ $settings['site_name'] ?? 'Pashupati Views Hotel' }}. All rights reserved.</p>
                <p>{{ $settings['footer_disclaimer'] ?? 'Crafted with ❤️ in Nepal' }}</p>
            </div>
        </div>
    </footer>

    {{-- ═══ MOBILE STICKY CTA BAR ═══ --}}
    <div class="mobile-cta-bar fixed bottom-0 inset-x-0 z-40 px-4 py-3 gap-2 shadow-2xl"
         style="background:rgba(13,27,42,0.97); border-top:1px solid rgba(184,149,59,0.2);">
        @if(!empty($settings['contact_phone']))
        <a href="tel:{{ $settings['contact_phone'] }}"
           class="flex-1 py-3 text-xs font-semibold text-center text-white rounded-xl bg-white/10 hover:bg-white/15 transition-colors"
           aria-label="Call us">
            📞 Call
        </a>
        @endif
        @if(!empty($settings['contact_whatsapp']))
        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
           target="_blank" rel="noopener"
           class="flex-1 py-3 text-xs font-semibold text-center text-white rounded-xl transition-colors"
           style="background:#25d366;" aria-label="WhatsApp">
            💬 WhatsApp
        </a>
        @endif
        <button onclick="openBooking()"
                class="flex-1 py-3 text-xs font-bold text-center text-white rounded-xl transition-colors"
                style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
            Book Now
        </button>
    </div>

    {{-- ═══ BOOKING MODAL ═══ --}}
    <dialog id="booking-dialog"
            class="w-full max-w-lg rounded-2xl p-0 shadow-2xl"
            style="background:#0d1b2a; border:1px solid rgba(184,149,59,0.2);"
            aria-labelledby="booking-title">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:rgba(184,149,59,0.15);">
            <div>
                <h2 id="booking-title" class="font-semibold text-white text-base">Plan Your Stay</h2>
                <p class="text-xs text-gray-400 mt-0.5">Fill in your preferences and we'll be in touch.</p>
            </div>
            <button onclick="closeBooking()" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors text-lg" aria-label="Close">&times;</button>
        </div>

        <form method="POST" action="{{ route('enquire') }}" class="px-6 py-5 space-y-4" id="booking-form">
            @csrf
            <input type="hidden" name="category" value="Room &amp; stay">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="b-checkin" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Check-in</label>
                    <input type="date" id="b-checkin" name="checkin"
                           class="w-full px-4 py-3 text-sm rounded-xl border text-white placeholder-gray-500 focus:outline-none transition-all"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(184,149,59,0.25); color-scheme:dark;"
                           min="{{ date('Y-m-d') }}">
                </div>
                <div>
                    <label for="b-checkout" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Check-out</label>
                    <input type="date" id="b-checkout" name="checkout"
                           class="w-full px-4 py-3 text-sm rounded-xl border text-white placeholder-gray-500 focus:outline-none transition-all"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(184,149,59,0.25); color-scheme:dark;">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="b-guests" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Guests</label>
                    <select id="b-guests" name="guests"
                            class="w-full px-4 py-3 text-sm rounded-xl border text-white focus:outline-none transition-all"
                            style="background:#1a2d42; border-color:rgba(184,149,59,0.25);">
                        @foreach([1,2,3,4,'5+'] as $g)
                            <option value="{{ $g }}" style="background:#1a2d42;">{{ $g }} {{ $g == 1 ? 'Guest' : 'Guests' }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="b-room" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Room Type</label>
                    <select id="b-room" name="message"
                            class="w-full px-4 py-3 text-sm rounded-xl border text-white focus:outline-none transition-all"
                            style="background:#1a2d42; border-color:rgba(184,149,59,0.25);">
                        <option value="Any room" style="background:#1a2d42;">Any Room</option>
                        @if(isset($rooms))
                            @foreach($rooms as $room)
                                <option value="{{ $room->name }}" style="background:#1a2d42;">{{ $room->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div>
                <label for="b-name" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Your Name <span class="text-red-400">*</span></label>
                <input type="text" id="b-name" name="guest_name" required
                       placeholder="Full name"
                       class="w-full px-4 py-3 text-sm rounded-xl border text-white placeholder-gray-500 focus:outline-none transition-all"
                       style="background:rgba(255,255,255,0.05); border-color:rgba(184,149,59,0.25);">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="b-phone" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Phone</label>
                    <input type="tel" id="b-phone" name="phone"
                           placeholder="+977…"
                           class="w-full px-4 py-3 text-sm rounded-xl border text-white placeholder-gray-500 focus:outline-none transition-all"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(184,149,59,0.25);">
                </div>
                <div>
                    <label for="b-email" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Email</label>
                    <input type="email" id="b-email" name="email"
                           placeholder="your@email.com"
                           class="w-full px-4 py-3 text-sm rounded-xl border text-white placeholder-gray-500 focus:outline-none transition-all"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(184,149,59,0.25);">
                </div>
            </div>

            <button type="submit"
                    class="w-full py-3.5 text-sm font-bold text-white rounded-xl transition-all hover:brightness-110 shadow-lg"
                    style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                Send Enquiry →
            </button>
            <p class="text-center text-xs text-gray-600 pb-1">We'll confirm availability within 24 hours.</p>
        </form>
    </dialog>

    {{-- ═══ SCRIPTS ═══ --}}
    <script>
    // ── Booking modal ──────────────────────────────────────────────
    const bookingDialog = document.getElementById('booking-dialog');
    function openBooking(roomName) {
        if (roomName) {
            const sel = document.getElementById('b-room');
            if (sel) { for (let o of sel.options) { if (o.value === roomName) o.selected = true; } }
        }
        bookingDialog?.showModal();
        document.body.style.overflow = 'hidden';
    }
    function closeBooking() {
        bookingDialog?.close();
        document.body.style.overflow = '';
    }
    bookingDialog?.addEventListener('close', () => {
        document.body.style.overflow = '';
    });
    bookingDialog?.addEventListener('click', e => {
        const rect = bookingDialog.getBoundingClientRect();
        if (e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom) closeBooking();
    });

    // ── Mobile nav ─────────────────────────────────────────────────
    const mobileNav  = document.getElementById('mobile-nav');
    const navToggle  = document.getElementById('nav-toggle');
    const iconMenu   = document.getElementById('icon-menu');
    const iconClose  = document.getElementById('icon-close');
    function closeMobileNav() {
        mobileNav?.classList.add('hidden');
        navToggle?.setAttribute('aria-expanded','false');
        iconMenu?.classList.remove('hidden');
        iconClose?.classList.add('hidden');
    }
    navToggle?.addEventListener('click', () => {
        const open = !mobileNav?.classList.contains('hidden');
        if (open) { closeMobileNav(); } else {
            mobileNav?.classList.remove('hidden');
            navToggle?.setAttribute('aria-expanded','true');
            iconMenu?.classList.add('hidden');
            iconClose?.classList.remove('hidden');
        }
    });

    // ── Language switcher ──────────────────────────────────────────
    let currentLang = localStorage.getItem('pvh_lang') || 'en';
    function setLang(lang) {
        currentLang = lang;
        localStorage.setItem('pvh_lang', lang);
        document.querySelectorAll('[data-lang]').forEach(el => {
            el.style.display = el.dataset.lang === lang ? '' : 'none';
        });
        document.querySelectorAll('.lang-btn').forEach(b => {
            const isActive = b.id === 'lang-' + lang || b.textContent.trim() === { en: 'EN', ne: 'ने', hi: 'हि' }[lang];
            b.style.background = isActive ? 'rgba(255,255,255,0.12)' : '';
            b.style.color = isActive ? '#fbbf24' : '';
        });
    }
    document.addEventListener('DOMContentLoaded', () => setLang(currentLang));

    // ── Sticky nav shadow on scroll ────────────────────────────────
    const header = document.getElementById('site-header');
    window.addEventListener('scroll', () => {
        header?.style.setProperty('box-shadow', window.scrollY > 10 ? '0 4px 32px rgba(0,0,0,0.4)' : 'none');
    }, { passive: true });

    // ── Show success flash after form submit then close dialog ──────
    @if(session('enquiry_success'))
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('enquiry-success-toast');
            if (el) { el.classList.remove('hidden'); setTimeout(() => el.classList.add('hidden'), 5000); }
        });
    @endif
    </script>

    {{-- Success toast --}}
    <div id="enquiry-success-toast"
         class="hidden fixed bottom-24 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-6 py-3.5 rounded-2xl text-sm font-medium text-white shadow-2xl"
         style="background:#1a5c2a; border:1px solid rgba(74,222,128,0.3);" role="alert">
        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
        Thank you! We've received your enquiry and will respond within 24 hours.
    </div>

    @include('frontend.sections.offer')

    @stack('scripts')

</body>
</html>
