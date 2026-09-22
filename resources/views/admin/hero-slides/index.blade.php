@extends('layouts.admin')
@section('title', 'Home Carousel')
@section('page_title', 'Home Carousel')
@section('content')
<div class="flex items-center justify-between mb-6 gap-4">
    <p class="text-sm text-gray-400">Manage hero images and videos. Slides appear in ascending sort order.</p>
    <a href="{{ route('admin.hero-slides.create') }}" class="px-5 py-3 bg-violet-600 text-white rounded-xl shrink-0">Add slide</a>
</div>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
@forelse($slides as $slide)
    <article class="rounded-2xl overflow-hidden bg-white/5 border border-white/10">
        @if($slide->media_type === 'video')
            <video src="{{ $slide->media_url }}" controls preload="metadata" class="w-full h-48 object-cover"></video>
        @else
            <img src="{{ $slide->media_url }}" alt="{{ $slide->title }}" class="w-full h-48 object-cover">
        @endif
        <div class="p-5 space-y-3">
            <h2 class="text-white font-semibold">{{ $slide->title }}</h2>
            <p class="text-sm text-gray-400">{{ ucfirst($slide->media_type) }} · Order {{ $slide->sort_order }} · {{ $slide->is_active ? 'Visible' : 'Hidden' }}</p>
            <div class="flex gap-4 items-center">
                <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="text-violet-400">Edit</a>
                <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}" data-confirm="Delete this slide and its media?">
                    @csrf @method('DELETE')
                    <button class="text-red-400">Delete</button>
                </form>
            </div>
        </div>
    </article>
@empty
    <p class="text-gray-400">No slides yet. Add an image or video to display the home carousel.</p>
@endforelse
</div>
@endsection
