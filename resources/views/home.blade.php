@extends('layouts.app')

@section('title', 'Welcome to Pashupati Views Hotel')
@section('meta_description', 'Experience the divine beauty of Pashupatinath. Book your stay at Pashupati Views Hotel – premier hotel in Kathmandu, Nepal.')

@section('content')

{{-- ===== HERO ===== --}}
<section class="relative min-h-[90vh] flex items-center justify-center bg-gray-900 overflow-hidden">

    {{-- Gradient overlay --}}
    <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-gray-900/90 to-amber-900/30"></div>

    {{-- Decorative circles --}}
    <div class="absolute top-20 right-20 w-72 h-72 bg-amber-400/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-20 left-10 w-96 h-96 bg-violet-600/10 rounded-full blur-3xl"></div>

    <div class="relative z-10 text-center px-4 max-w-4xl mx-auto">
        <span class="inline-block text-amber-400 text-xs font-bold uppercase tracking-widest mb-4 px-4 py-1.5 bg-amber-400/10 rounded-full border border-amber-400/20">
            ✨ Premier Hotel in Kathmandu
        </span>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-white leading-tight mb-6">
            Experience the<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 to-amber-600">
                Divine Beauty
            </span>
        </h1>
        <p class="text-gray-400 text-lg sm:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
            Nestled beside the sacred Pashupatinath Temple, our hotel offers world-class hospitality with breathtaking views of Kathmandu Valley.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
            <a href="{{ url('/book') }}"
               class="px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-white font-semibold rounded-xl shadow-lg shadow-amber-900/40 hover:-translate-y-0.5 active:translate-y-0 transition-all text-sm">
                Book Your Stay
            </a>
            <a href="{{ url('/rooms') }}"
               class="px-8 py-3.5 bg-white/10 hover:bg-white/15 text-white font-medium rounded-xl border border-white/10 hover:-translate-y-0.5 transition-all text-sm">
                Explore Rooms
            </a>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-gray-500 text-xs">
        <span>Scroll</span>
        <svg class="w-4 h-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</section>

{{-- ===== FEATURES / USPs ===== --}}
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-14">
            <h2 class="text-3xl font-bold text-gray-900">Why Choose Us?</h2>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">We go beyond hospitality — we offer an experience you will cherish forever.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                $features = [
                    ['icon' => '🌄', 'title' => 'Stunning Views',    'desc' => 'Wake up to panoramic views of Pashupatinath Temple and Kathmandu Valley.'],
                    ['icon' => '🍽️', 'title' => 'Fine Dining',       'desc' => 'Savour authentic Nepali and international cuisine prepared by expert chefs.'],
                    ['icon' => '🧖', 'title' => 'Wellness & Spa',    'desc' => 'Rejuvenate with traditional Ayurvedic treatments and modern spa therapies.'],
                    ['icon' => '🛡️', 'title' => '24/7 Concierge',   'desc' => 'Our dedicated team is available around the clock to cater to your every need.'],
                ];
            @endphp

            @foreach($features as $f)
                <div class="text-center p-6 rounded-2xl border border-gray-100 hover:border-amber-200 hover:shadow-lg hover:-translate-y-1 transition-all duration-200 group">
                    <div class="text-4xl mb-4 group-hover:scale-110 transition-transform duration-200">{{ $f['icon'] }}</div>
                    <h3 class="font-semibold text-gray-900 mb-2">{{ $f['title'] }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ $f['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===== ROOMS TEASER ===== --}}
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-12">
            <div>
                <h2 class="text-3xl font-bold text-gray-900">Our Rooms & Suites</h2>
                <p class="text-gray-500 mt-2">Elegant comfort for every traveller.</p>
            </div>
            <a href="{{ url('/rooms') }}" class="hidden sm:inline-flex text-sm font-medium text-amber-600 hover:text-amber-700 transition-colors">
                View all rooms →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-7">
            @php
                $rooms = [
                    ['name' => 'Deluxe Room',    'price' => 'from NPR 5,000/night', 'badge' => 'Popular',  'emoji' => '🛏️'],
                    ['name' => 'Garden Suite',   'price' => 'from NPR 9,000/night', 'badge' => 'Peaceful', 'emoji' => '🌿'],
                    ['name' => 'Temple View Suite','price'=> 'from NPR 14,000/night','badge'=> 'Premium',  'emoji' => '🏛️'],
                ];
            @endphp

            @foreach($rooms as $room)
                <div class="group rounded-2xl overflow-hidden border border-gray-200 bg-white hover:shadow-xl transition-all duration-300">
                    <div class="h-44 bg-gradient-to-br from-gray-100 to-amber-50 flex items-center justify-center text-6xl group-hover:scale-105 transition-transform duration-300">
                        {{ $room['emoji'] }}
                    </div>
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-1">
                            <h3 class="font-semibold text-gray-900">{{ $room['name'] }}</h3>
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">{{ $room['badge'] }}</span>
                        </div>
                        <p class="text-sm text-amber-600 font-medium mb-4">{{ $room['price'] }}</p>
                        <a href="{{ url('/book') }}"
                           class="block text-center py-2.5 text-sm font-semibold bg-gray-900 hover:bg-amber-500 text-white rounded-xl transition-colors duration-200">
                            Book Now
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-8 sm:hidden">
            <a href="{{ url('/rooms') }}" class="text-sm font-medium text-amber-600">View all rooms →</a>
        </div>
    </div>
</section>

{{-- ===== CTA BANNER ===== --}}
<section class="py-20 bg-gray-900 text-center px-4">
    <div class="max-w-2xl mx-auto">
        <h2 class="text-3xl font-bold text-white mb-4">Ready for an Unforgettable Stay?</h2>
        <p class="text-gray-400 mb-8">Book directly with us for the best rates and exclusive benefits.</p>
        <a href="{{ url('/book') }}"
           class="inline-block px-10 py-4 bg-amber-500 hover:bg-amber-400 text-white font-bold rounded-xl shadow-lg shadow-amber-900/40 hover:-translate-y-0.5 transition-all">
            Check Availability
        </a>
    </div>
</section>

@endsection
