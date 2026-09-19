@extends('layouts.app')

@section('title', 'Gallery — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('meta_description', 'View our full photo gallery and explore the spaces at Pashupati Views Hotel.')

@section('content')

    {{-- Hero block for the standalone gallery page --}}
    <div class="relative pt-24 pb-12 sm:pt-32 sm:pb-16 text-center" style="background:#0d1b2a;">
        <div class="absolute inset-0 opacity-10" style="background-image:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23b8953b\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        <div class="relative max-w-3xl mx-auto px-4 z-10">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white mb-4" style="font-family:'Noto Sans Devanagari', 'Inter', sans-serif;">
                Our Photo Gallery
            </h1>
            <p class="text-gray-400 text-sm md:text-base mb-6 max-w-xl mx-auto leading-relaxed">
                Explore a visual journey of Pashupati Views Hotel.
            </p>
            <div class="divider-gold mx-auto relative opacity-50"></div>
        </div>
    </div>

    {{-- Re-use the gallery section component without a limit constraint --}}
    @include('frontend.sections.gallery')

@endsection
