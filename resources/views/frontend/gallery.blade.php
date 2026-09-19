@extends('layouts.app')

@section('title', e('Gallery — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel')))
@section('meta_description', 'Explore the rooms, dining spaces, and surroundings of Pashupati Views Hotel through our photo and video gallery.')

@section('content')
@php
    $categories = $galleryItems->groupBy(fn ($item) => strtolower(trim($item->section ?? '')) ?: 'general');
@endphp
<div class="gallery-page">
    <section aria-labelledby="gallery-page-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <nav aria-label="Breadcrumb" class="gallery-breadcrumb">
                <a href="{{ route('home') }}">Home</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">Gallery</span>
            </nav>
        </div>
    </section>

    <section id="gallery" class="gallery-collection" aria-labelledby="gallery-collection-title">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="gallery-collection-heading">
                <div>
                    <p class="section-label">The collection</p>
                    <h2 id="gallery-collection-title">A feel for your stay.</h2>
                </div>
                @if($galleryItems->isNotEmpty())
                <p class="gallery-collection-hint">Select a photo or video to take a closer look.</p>
                @endif
            </div>

            @if($galleryItems->isNotEmpty())
            <div class="gallery-toolbar">
                <div data-gallery-filters hidden role="group" aria-label="Filter gallery by category" class="gallery-category-filters">
                    <button type="button" data-gallery-filter="" data-gallery-label="All moments" aria-pressed="true" aria-controls="gallery-grid">
                        All moments <span aria-hidden="true">{{ $galleryItems->count() }}</span>
                    </button>
                    @foreach($categories as $category => $items)
                    <button type="button" data-gallery-filter="{{ $category }}" data-gallery-label="{{ \Illuminate\Support\Str::headline($category) }}" aria-pressed="false" aria-controls="gallery-grid">
                        {{ \Illuminate\Support\Str::headline($category) }} <span aria-hidden="true">{{ $items->count() }}</span>
                    </button>
                    @endforeach
                </div>
                <p data-gallery-count role="status" aria-live="polite" aria-atomic="true" class="gallery-result-count">{{ $galleryItems->count() }} {{ $galleryItems->count() === 1 ? 'moment' : 'moments' }}</p>
            </div>

            <div id="gallery-grid" class="gallery-editorial-grid">
                @foreach($galleryItems as $item)
                @php
                    $category = strtolower(trim($item->section ?? '')) ?: 'general';
                    $isVideo = $item->media_type === 'video';
                    $title = $item->title ?: ($isVideo ? 'A moment in motion' : 'A glimpse of our hotel');
                @endphp
                <article class="gallery-editorial-card" data-gallery-category="{{ $category }}" @if($loop->first) data-gallery-featured @endif>
                    <div class="gallery-editorial-media">
                        <div class="gallery-media-fallback" @if($item->image_url) hidden @endif data-gallery-fallback>
                            <svg viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="1" aria-hidden="true"><rect x="7" y="7" width="34" height="34" rx="2"/><circle cx="17" cy="18" r="4"/><path d="m7 35 12-12 8 8 6-6 8 8"/></svg>
                            <span>{{ $isVideo ? 'Video preview unavailable' : 'Photo unavailable' }}</span>
                        </div>
                        @if($item->image_url)
                            @if($isVideo)
                            <video src="{{ $item->image_url }}" class="gallery-editorial-image" muted playsinline preload="metadata" aria-label="{{ $title }}" data-gallery-preview></video>
                            @else
                            <img src="{{ $item->image_url }}" alt="{{ $title }}" class="gallery-editorial-image" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async" data-gallery-preview>
                            @endif
                            <div class="gallery-card-shade" aria-hidden="true"></div>
                            <button type="button" data-gallery-open data-media-src="{{ $item->image_url }}" data-media-type="{{ $item->media_type }}" data-media-title="{{ $title }}"
                                    aria-haspopup="dialog" aria-controls="gallery-viewer" aria-label="{{ $isVideo ? 'Play' : 'Enlarge' }} {{ $title }}" class="gallery-card-open">
                                <span class="gallery-card-action" aria-hidden="true">
                                    @if($isVideo)
                                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="m9 5 11 7-11 7z"/></svg>
                                    @else
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3H3v5m13-5h5v5M3 16v5h5m13-5v5h-5"/></svg>
                                    @endif
                                </span>
                                <span class="gallery-card-open-label" aria-hidden="true">{{ $isVideo ? 'Play video' : 'View photo' }} <span>↗</span></span>
                            </button>
                        @endif
                        @if($item->badge_label)
                        <span class="gallery-card-badge">{{ $item->badge_label }}</span>
                        @endif
                    </div>
                    <div class="gallery-card-caption">
                        <div class="min-w-0">
                            <p>{{ \Illuminate\Support\Str::headline($category) }} @if($isVideo) <span aria-hidden="true">/</span> Video @endif</p>
                            <h3>{{ $title }}</h3>
                        </div>
                        <span class="gallery-card-number" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </article>
                @endforeach
            </div>
            <div class="gallery-endnote"><span></span><p>A glimpse today. A memory tomorrow.</p><span></span></div>
            @else
            <div class="gallery-empty">
                <div class="divider-gold mx-auto mb-6"></div>
                <h3>New perspectives, coming soon.</h3>
                <p>Our gallery is being prepared. In the meantime, explore the hotel and plan your stay.</p>
                <a href="{{ route('home') }}#rooms">Explore our rooms <span aria-hidden="true">↗</span></a>
            </div>
            @endif
        </div>
    </section>
</div>
@if($galleryItems->isNotEmpty())
    @include('frontend.partials.gallery-viewer')
@endif
@endsection
