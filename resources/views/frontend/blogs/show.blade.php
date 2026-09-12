@extends('layouts.app')
@section('title', $blog->title . ' — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('meta_description', $blog->summary)
@section('content')
<article class="bg-[#fffdf9] py-12 sm:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('blogs.index') }}" class="text-sm font-medium text-[#856534] hover:underline">← All blogs</a>
        <header class="mt-8 mb-8">
            <time datetime="{{ $blog->published_at->toDateString() }}" class="text-sm text-[#856534]">{{ $blog->published_at->format('F j, Y') }}</time>
            <h1 class="mt-4 text-3xl sm:text-5xl font-bold leading-tight text-navy break-words">{{ $blog->title }}</h1>
            @if($blog->excerpt)
                <p class="mt-5 text-lg leading-relaxed text-gray-600 break-words">{{ $blog->excerpt }}</p>
            @endif
        </header>
        @if($blog->cover_url)
            <img src="{{ $blog->cover_url }}" alt="{{ $blog->title }}" class="mb-10 w-full max-h-[560px] rounded-2xl object-cover">
        @endif
        <div class="whitespace-pre-wrap break-words text-base sm:text-lg leading-loose text-gray-700">{{ $blog->content }}</div>
        <div class="mt-12 border-t border-gold/15 pt-8">
            <a href="{{ route('blogs.index') }}" class="font-semibold text-[#856534] hover:underline">Explore more stories →</a>
        </div>
    </div>
</article>
@endsection
