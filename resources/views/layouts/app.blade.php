<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="@yield('meta_description', 'Pashupati Views Hotel – A premium hotel experience in Nepal.')">
    <title>@yield('title', 'Pashupati Views Hotel')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="bg-white text-gray-900 font-sans antialiased">

    {{-- ===== NAVIGATION ===== --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    <span class="text-2xl">🏨</span>
                    <div class="leading-tight">
                        <div class="text-sm font-bold text-gray-900 tracking-tight">Pashupati Views</div>
                        <div class="text-xs text-amber-600 font-medium tracking-widest uppercase">Hotel</div>
                    </div>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-gray-600">
                    <a href="{{ url('/') }}"        class="hover:text-amber-600 transition-colors">Home</a>
                    <a href="{{ url('/rooms') }}"   class="hover:text-amber-600 transition-colors">Rooms</a>
                    <a href="{{ url('/amenities') }}" class="hover:text-amber-600 transition-colors">Amenities</a>
                    <a href="{{ url('/gallery') }}" class="hover:text-amber-600 transition-colors">Gallery</a>
                    <a href="{{ url('/contact') }}" class="hover:text-amber-600 transition-colors">Contact</a>
                </nav>

                {{-- CTA --}}
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ url('/book') }}"
                       class="px-4 py-2 text-sm font-semibold bg-amber-500 hover:bg-amber-600 text-white rounded-lg transition-colors shadow-sm">
                        Book Now
                    </a>
                </div>

                {{-- Mobile hamburger --}}
                <button id="nav-toggle" class="md:hidden p-2 rounded-lg hover:bg-gray-100 transition-colors" aria-label="Toggle navigation">
                    <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Nav --}}
        <div id="mobile-nav" class="hidden md:hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 flex flex-col gap-3 text-sm font-medium text-gray-700">
                <a href="{{ url('/') }}"          class="hover:text-amber-600">Home</a>
                <a href="{{ url('/rooms') }}"     class="hover:text-amber-600">Rooms</a>
                <a href="{{ url('/amenities') }}" class="hover:text-amber-600">Amenities</a>
                <a href="{{ url('/gallery') }}"   class="hover:text-amber-600">Gallery</a>
                <a href="{{ url('/contact') }}"   class="hover:text-amber-600">Contact</a>
                <a href="{{ url('/book') }}"      class="mt-1 px-4 py-2 text-center bg-amber-500 text-white rounded-lg font-semibold">Book Now</a>
            </div>
        </div>
    </header>

    {{-- ===== PAGE CONTENT ===== --}}
    <main>
        @yield('content')
    </main>

    {{-- ===== FOOTER ===== --}}
    <footer class="bg-gray-900 text-gray-300 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">

                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="text-2xl">🏨</span>
                        <div>
                            <div class="text-white font-bold text-lg">Pashupati Views Hotel</div>
                            <div class="text-amber-400 text-xs font-medium tracking-widest uppercase">Nepal</div>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed text-gray-400 max-w-xs">
                        Experience the divine beauty of Pashupatinath from the comfort of our premier hotel, offering world-class hospitality in the heart of Kathmandu.
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wide">Quick Links</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ url('/rooms') }}"   class="hover:text-amber-400 transition-colors">Rooms & Suites</a></li>
                        <li><a href="{{ url('/amenities') }}" class="hover:text-amber-400 transition-colors">Amenities</a></li>
                        <li><a href="{{ url('/gallery') }}" class="hover:text-amber-400 transition-colors">Gallery</a></li>
                        <li><a href="{{ url('/book') }}"    class="hover:text-amber-400 transition-colors">Book Now</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-semibold text-sm mb-4 uppercase tracking-wide">Contact</h4>
                    <ul class="space-y-2 text-sm">
                        <li>📍 Pashupatinath, Kathmandu, Nepal</li>
                        <li>📞 +977-1-XXXXXXX</li>
                        <li>✉️ info@pashupativiews.com</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-10 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500">
                <p>&copy; {{ date('Y') }} Pashupati Views Hotel. All rights reserved.</p>
                <p>Crafted with ❤️ in Nepal</p>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('nav-toggle')?.addEventListener('click', () => {
            document.getElementById('mobile-nav')?.classList.toggle('hidden');
        });
    </script>

    @stack('scripts')
</body>
</html>
