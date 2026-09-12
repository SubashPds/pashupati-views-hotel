@extends('layouts.app')
@section('title', 'Our Blog — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('content')
<section class="bg-ivory py-16 sm:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-12">
            <a href="{{ route('home') }}" class="text-sm text-[#856534] hover:underline">← Back to home</a>
            <h1 class="mt-6 text-4xl sm:text-5xl font-bold text-navy">The hotel journal.</h1>
            <p class="mt-4 text-gray-600 leading-relaxed">Stories, local discoveries, and inspiration for your next stay in Kathmandu.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($blogs as $blog)
                @include('frontend.blogs.card')
            @empty
                <p class="col-span-full py-12 text-center text-gray-600">Our first stories are on their way. Check back soon.</p>
            @endforelse
        </div>
        <div class="mt-10">{{ $blogs->links() }}</div>
    </div>
</section>
@endsection
