<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') — Pashupati Views Hotel</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full font-sans antialiased bg-gray-950 text-gray-100">

    <div class="flex h-screen overflow-hidden">

        {{-- ===== SIDEBAR ===== --}}
        <aside id="sidebar"
               class="flex flex-col w-64 shrink-0 bg-gray-900 border-r border-white/5 transition-transform duration-300 ease-in-out
                      fixed inset-y-0 left-0 z-50
                      md:relative md:translate-x-0"
               aria-label="Sidebar navigation">

            {{-- Brand --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-white/5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-600 to-purple-500 flex items-center justify-center text-lg shadow-lg shadow-violet-900/40">
                    🏨
                </div>
                <div class="leading-tight overflow-hidden">
                    <p class="text-white font-semibold text-sm truncate">Pashupati Views</p>
                    <p class="text-violet-400 text-xs font-medium">Admin Panel</p>
                </div>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
                <p class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-widest">Main</p>

                @php
                    $navItems = [
                        ['route' => 'admin.dashboard',          'icon' => '📊', 'label' => 'Dashboard'],
                        ['route' => 'admin.rooms.index',        'icon' => '🛏️', 'label' => 'Rooms'],
                        ['route' => 'admin.packages.index',     'icon' => '🎁', 'label' => 'Packages'],
                        ['route' => 'admin.experiences.index',  'icon' => '✨', 'label' => 'Experiences'],
                        ['route' => 'admin.hero-slides.index', 'icon' => '🎞️', 'label' => 'Home Carousel'],
                        ['route' => 'admin.gallery.index',      'icon' => '🖼️', 'label' => 'Gallery'],
                        ['route' => 'admin.services.index',     'icon' => '🛎️', 'label' => 'Services'],
                        ['route' => 'admin.testimonials.index', 'icon' => '💬', 'label' => 'Testimonials'],
                        ['route' => 'admin.enquiries.index',    'icon' => '📩', 'label' => 'Enquiries'],
                        ['route' => 'admin.settings.index',     'icon' => '⚙️', 'label' => 'Site Settings'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                              {{ request()->routeIs($item['route'])
                                    ? 'bg-violet-600/20 text-violet-300 ring-1 ring-violet-500/30'
                                    : 'text-gray-400 hover:bg-white/5 hover:text-gray-100' }}">
                        <span class="text-base leading-none">{{ $item['icon'] }}</span>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- User + Logout --}}
            <div class="px-3 py-4 border-t border-white/5">
                <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-white/5 mb-3">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-600 to-purple-500 flex items-center justify-center text-xs font-bold text-white shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? '' }}</p>
                        <p class="text-xs text-gray-500">Super Admin</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" id="btn-logout"
                            class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div id="sidebar-overlay"
             class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden hidden"
             aria-hidden="true">
        </div>

        {{-- ===== MAIN AREA ===== --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

            {{-- Top bar --}}
            <header class="flex items-center gap-4 px-4 sm:px-6 h-14 border-b border-white/5 bg-gray-900/60 backdrop-blur-md shrink-0">
                {{-- Mobile menu toggle --}}
                <button id="sidebar-toggle"
                        class="md:hidden p-2 rounded-lg hover:bg-white/10 transition-colors"
                        aria-label="Open sidebar">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="flex-1">
                    <h1 class="text-sm font-semibold text-white">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('breadcrumb')
                        <nav class="text-xs text-gray-500 mt-0.5">@yield('breadcrumb')</nav>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    {{-- Flash success --}}
                    @if(session('success'))
                        <span class="hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400 bg-emerald-500/10 ring-1 ring-emerald-500/20 rounded-lg px-3 py-1.5">
                            ✓ {{ session('success') }}
                        </span>
                    @endif

                    <span class="hidden sm:inline-flex text-xs text-gray-500">
                        {{ now()->format('D, d M Y') }}
                    </span>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

                {{-- Global error alert --}}
                @if($errors->any())
                    <div class="mb-6 flex items-start gap-3 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"></path>
                        </svg>
                        <ul class="space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const toggle  = document.getElementById('sidebar-toggle');

        toggle?.addEventListener('click', () => {
            sidebar?.classList.toggle('-translate-x-full');
            overlay?.classList.toggle('hidden');
        });
        overlay?.addEventListener('click', () => {
            sidebar?.classList.add('-translate-x-full');
            overlay?.classList.add('hidden');
        });
    </script>

    @stack('scripts')
</body>
</html>
