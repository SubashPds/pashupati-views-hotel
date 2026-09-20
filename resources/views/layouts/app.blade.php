<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO --}}
    <title>@yield('title', 'Pashupati Views Hotel — Where Comfort Meets Devotion')</title>
    <meta name="description" content="@yield('meta_description', 'A premium hotel experience beside the sacred Pashupatinath Temple, Kathmandu, Nepal. Book your stay and discover a quieter rhythm.')">
    <meta property="og:title" content="@yield('title', 'Pashupati Views Hotel')">
    <meta property="og:description" content="@yield('meta_description', 'A premium hotel experience in Kathmandu, Nepal.')">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">

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
        class="sticky top-0 z-50"
        style="background: #0d1b2a; border-bottom: 1px solid rgba(184,149,59,0.15);">

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
                <!-- for restaurant add 
                   [route('restaurant'),          'Restaurant'], -->
                <nav class="hidden xl:flex items-center gap-0 text-sm font-medium" aria-label="Main navigation">
                    @foreach([
                    ['#home', 'Home'],
                    ['#rooms', 'Rooms'],
                    ['#packages', 'Packages'],
                    ['#experience', 'Experience'],
                    ['#services', 'Services'],
                    [route('gallery'), 'Gallery'],
                    [route('blogs.index'), 'Blogs'],
                    [route('faqs.index'), 'FAQ'],
                    [route('contact'), 'Contact'],
                    ] as [$href, $label])
                    @php
                    $resolvedHref = str_starts_with($href, '#') && !request()->routeIs('home')
                    ? route('home') . $href
                    : $href;
                    @endphp
                    <a href="{{ $resolvedHref }}"
                        @if($href===route('blogs.index') && request()->routeIs('blogs.*')) aria-current="location" @endif
                        @if($href === route('faqs.index') && request()->routeIs('faqs.*')) aria-current="location" @endif
                        @if($href === route('gallery') && request()->routeIs('gallery')) aria-current="location" @endif
                        {{-- @if($href === route('restaurant') && request()->routeIs('restaurant')) aria-current="location" @endif --}}
                        class="nav-link px-2 py-2 rounded-lg text-gray-300 hover:text-amber-300 hover:bg-white/5 transition-all">
                        {{ $label }}
                    </a>
                    @endforeach
                </nav>

                {{-- Right actions --}}
                <div class="flex items-center gap-2">
                    @isset($currency)
                    <form method="POST" action="{{ route('currency.store') }}" data-ajax-form="currency" class="relative">
                        @csrf
                        <input type="hidden" name="return_to" value="{{ request()->getPathInfo() }}">
                        <label for="display-currency" class="sr-only">Display currency</label>
                        <select id="display-currency" name="currency" onchange="this.form.requestSubmit()"
                            class="rounded-lg border border-white/20 bg-navy px-1 py-2 text-xs text-white" style="background:#0d1b2a;">
                            @foreach(['NPR', 'INR', 'USD'] as $code)
                            <option value="{{ $code }}" @selected($currency->code === $code)>{{ $code }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="text-xs text-white">Apply</button></noscript>
                        @include('frontend.partials.form-feedback')
                    </form>
                    @endisset
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
                        <svg id="icon-menu" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Nav Drawer --}}
        <div id="mobile-nav"
            class="hidden xl:hidden border-t"
            style="border-color:rgba(184,149,59,0.15); background:rgba(13,27,42,0.98);">
            <div class="px-4 py-4 flex flex-col gap-1 text-sm font-medium">
                {{--use this for restaurant =>  [route('restaurant'),      'Restaurant'],--}}
                @foreach([
                ['#home', 'Home'],
                ['#rooms', 'Rooms'],
                ['#packages', 'Packages'],
                ['#experience', 'Experience'],
                ['#services', 'Services'],
                [route('gallery'), 'Gallery'],
                [route('blogs.index'), 'Blogs'],
                [route('faqs.index'), 'FAQ'],
                [route('contact'), 'Contact'],
                ] as [$href, $label])
                @php
                $resolvedHref = str_starts_with($href, '#') && !request()->routeIs('home')
                ? route('home') . $href
                : $href;
                @endphp
                <a href="{{ $resolvedHref }}"
                    @if($href===route('blogs.index') && request()->routeIs('blogs.*')) aria-current="location" @endif
                    @if($href === route('faqs.index') && request()->routeIs('faqs.*')) aria-current="location" @endif
                    @if($href === route('gallery') && request()->routeIs('gallery')) aria-current="location" @endif
                    {{--@if($href === route('restaurant') && request()->routeIs('restaurant')) aria-current="location" @endif--}}
                    class="nav-link px-4 py-3 rounded-xl text-gray-300 hover:text-amber-300 hover:bg-white/5 transition-all"
                    onclick="closeMobileNav()">
                    {{ $label }}
                </a>
                @endforeach

                <button onclick="openBooking(); closeMobileNav()"
                    class="mt-2 py-3 px-6 text-sm font-bold text-center text-white rounded-xl transition-all"
                    style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                    Book Your Stay ↗
                </button>
            </div>
        </div>
    </header>

    @if($showCountryPrompt ?? false)
    <section data-country-prompt aria-labelledby="country-prompt-title" class="border-b border-amber-200 bg-amber-50 px-4 py-4">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="country-prompt-title" class="text-sm font-semibold text-navy">Where are you visiting from?</h2>
                <p class="mt-1 text-xs text-gray-600">Choose your location to see prices in your currency. We’ll remember your choice for one year.</p>
            </div>
            <form method="POST" action="{{ route('currency.store') }}" data-ajax-form="currency" class="flex flex-wrap gap-2">
                @csrf
                <input type="hidden" name="return_to" value="{{ request()->getPathInfo() }}">
                @foreach(['NP' => 'Nepal · NPR', 'IN' => 'India · INR', 'OTHER' => 'Other country · USD'] as $country => $label)
                <button type="submit" name="country" value="{{ $country }}" class="rounded-lg border border-amber-300 bg-white px-3 py-2 text-xs font-medium text-navy hover:bg-amber-100">{{ $label }}</button>
                @endforeach
                @include('frontend.partials.form-feedback')
            </form>
        </div>
    </section>
    @endif

    {{-- ═══ PAGE CONTENT ═══ --}}
    <main id="home">
        @yield('content')
    </main>

    {{-- ═══ CONTACT (global — shown on every page before footer) ═══ --}}
    @include('frontend.sections.contact')

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
                        @foreach([['#rooms','Rooms & Suites'],['#experience','Experiences'],['#services','Services'],[route('gallery'),'Gallery'],[route('blogs.index'),'Blogs'],[route('faqs.index'),'FAQ'],[route('contact'),'Contact']] as [$h,$l])
                        <li><a href="{{ str_starts_with($h, '#') && !request()->routeIs('home') ? route('home') . $h : $h }}" class="hover:text-amber-300 transition-colors">{{ $l }}</a></li>
                        @endforeach
                    </ul>
                </div>

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
                            <span class="min-w-0">
                                @foreach(\App\Support\EmailAddresses::valid($settings['contact_email']) as $contactEmail)
                                <a href="mailto:{{ $contactEmail }}" class="block break-all hover:text-amber-300 transition-colors">{{ $contactEmail }}</a>
                                @endforeach
                            </span>
                        </li>
                        @endif
                    </ul>

                    @if(!empty($settings['social_facebook']) || !empty($settings['social_instagram']) || !empty($settings['social_tiktok']))
                    <div class="mt-6 pt-5 border-t" style="border-color:rgba(184,149,59,0.15);">
                        <h4 class="text-white font-semibold text-[10px] uppercase tracking-widest mb-3" style="color:#b8953b;">Follow Us</h4>
                        <div class="flex items-center gap-4">
                            @if(!empty($settings['social_facebook']))
                            <a href="{{ $settings['social_facebook'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-amber-300 transition-colors" aria-label="Facebook">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            @endif
                            @if(!empty($settings['social_instagram']))
                            <a href="{{ $settings['social_instagram'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-amber-300 transition-colors" aria-label="Instagram">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            @endif
                            @if(!empty($settings['social_tiktok']))
                            <a href="{{ $settings['social_tiktok'] }}" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-amber-300 transition-colors" aria-label="TikTok">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 30 30" aria-hidden="true">
                                    <path d="M16.656 1.029c1.637-0.025 3.262-0.012 4.886-0.025 0.054 2.031 0.878 3.859 2.189 5.213l-0.002-0.002c1.411 1.271 3.247 2.095 5.271 2.235l0.028 0.002v5.036c-1.912-0.048-3.71-0.489-5.331-1.247l0.082 0.034c-0.784-0.377-1.447-0.764-2.077-1.196l0.052 0.034c-0.012 3.649 0.012 7.298-0.025 10.934-0.103 1.853-0.719 3.543-1.707 4.954l0.020-0.031c-1.652 2.366-4.328 3.919-7.371 4.011l-0.014 0c-0.123 0.006-0.268 0.009-0.414 0.009-1.73 0-3.347-0.482-4.725-1.319l0.040 0.023c-2.508-1.509-4.238-4.091-4.558-7.094l-0.004-0.041c-0.025-0.625-0.037-1.25-0.012-1.862 0.49-4.779 4.494-8.476 9.361-8.476 0.547 0 1.083 0.047 1.604 0.136l-0.056-0.008c0.025 1.849-0.050 3.699-0.050 5.548-0.423-0.153-0.911-0.242-1.42-0.242-1.868 0-3.457 1.194-4.045 2.861l-0.009 0.030c-0.133 0.427-0.21 0.918-0.21 1.426 0 0.206 0.013 0.41 0.037 0.61l-0.002-0.024c0.332 2.046 2.086 3.59 4.201 3.59 0.061 0 0.121-0.001 0.181-0.004l-0.009 0c1.463-0.044 2.733-0.831 3.451-1.994l0.010-0.018c0.267-0.372 0.45-0.822 0.511-1.311l0.001-0.014c0.125-2.237 0.075-4.461 0.087-6.698 0.012-5.036-0.012-10.060 0.025-15.083z"></path>
                                </svg>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <div class="border-t mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600"
                style="border-color:rgba(184,149,59,0.10);">
                <p>© {{ date('Y') }} {{ $settings['site_name'] ?? 'Pashupati Views Hotel' }}. All rights reserved.</p>
                {{-- Policies --}}
                <div>
                        @if(isset($policies) && $policies->get(\App\Models\Policy::PRIVACY_POLICY)?->is_active)
                       <a href="{{ route('privacy-policy') }}" class="hover:text-amber-300 transition-colors">Privacy Policy</a>
                        @endif
                        @if(isset($policies) && $policies->get(\App\Models\Policy::TERMS_AND_CONDITIONS)?->is_active)
                       <a href="{{ route('terms-and-conditions') }}" class="hover:text-amber-300 transition-colors">Terms & Conditions</a>
                        @endif
                </div>
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

        <form method="POST" action="{{ route('enquire') }}" data-ajax-form="enquiry" class="px-6 py-5 space-y-4" id="booking-form">
            @csrf
            @include('frontend.partials.form-feedback')
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
                if (sel) {
                    for (let o of sel.options) {
                        if (o.value === roomName) o.selected = true;
                    }
                }
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
        const mobileNav = document.getElementById('mobile-nav');
        const navToggle = document.getElementById('nav-toggle');
        const iconMenu = document.getElementById('icon-menu');
        const iconClose = document.getElementById('icon-close');

        function closeMobileNav() {
            mobileNav?.classList.add('hidden');
            navToggle?.setAttribute('aria-expanded', 'false');
            iconMenu?.classList.remove('hidden');
            iconClose?.classList.add('hidden');
        }
        navToggle?.addEventListener('click', () => {
            const open = !mobileNav?.classList.contains('hidden');
            if (open) {
                closeMobileNav();
            } else {
                mobileNav?.classList.remove('hidden');
                navToggle?.setAttribute('aria-expanded', 'true');
                iconMenu?.classList.add('hidden');
                iconClose?.classList.remove('hidden');
            }
        });

        // ── Sticky nav shadow on scroll ────────────────────────────────
        const header = document.getElementById('site-header');
        let headerHasShadow;

        function syncHeaderShadow() {
            const scrolled = window.scrollY > 10;
            if (scrolled === headerHasShadow) return;
            headerHasShadow = scrolled;
            header?.style.setProperty('box-shadow', scrolled ? '0 4px 32px rgba(0,0,0,0.4)' : 'none');
        }
        window.addEventListener('scroll', syncHeaderShadow, {
            passive: true
        });
        window.addEventListener('pageshow', syncHeaderShadow);
        syncHeaderShadow();

        // ── Show success flash after form submit then close dialog ──────
        @if(session('enquiry_success'))
        document.addEventListener('DOMContentLoaded', () => {
            const el = document.getElementById('enquiry-success-toast');
            if (el) {
                el.classList.remove('hidden');
                setTimeout(() => el.classList.add('hidden'), 5000);
            }
        });
        @endif
    </script>

    {{-- Success toast --}}
    <div id="enquiry-success-toast"
        class="hidden fixed bottom-24 sm:bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-3 px-6 py-3.5 rounded-2xl text-sm font-medium text-white shadow-2xl"
        style="background:#1a5c2a; border:1px solid rgba(74,222,128,0.3);" role="alert">
        <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
        </svg>
        Thank you! We've received your enquiry and will respond within 24 hours.
    </div>

    @include('frontend.sections.offer')

    @stack('scripts')

</body>

</html>