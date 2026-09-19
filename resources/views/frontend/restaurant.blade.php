@extends('layouts.app')
@section('title', e(($restaurant?->name ?? 'Restaurant') . ' — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel')))
@section('meta_description', e($restaurant?->description ?? ''))

@section('content')
<section class="bg-navy px-4 py-16 text-center sm:px-6 sm:py-24" aria-labelledby="restaurant-title">
    <div class="mx-auto max-w-3xl">
        <p class="section-label mb-5">Restaurant</p>
        <h1 id="restaurant-title" class="break-words text-3xl font-bold tracking-tight text-white sm:text-4xl lg:text-5xl">{{ $restaurant?->name ?? 'Restaurant' }}</h1>
        @if($restaurant?->description)
        <p class="mx-auto mt-6 max-w-2xl whitespace-pre-line break-words text-sm leading-relaxed text-gray-300 sm:text-base">{{ $restaurant->description }}</p>
        @endif
        <div class="divider-gold mx-auto mt-8"></div>
        @if($restaurant?->menu_image)
        <a href="#food-menu" class="mt-8 inline-flex rounded-lg bg-gradient-to-br from-gold to-gold-light px-6 py-3 text-sm font-semibold text-navy focus-visible:outline-2 focus-visible:outline-gold-light">Explore the menu ↓</a>
        @endif
    </div>
</section>

@if($restaurant)
<div class="mx-auto max-w-7xl space-y-14 px-4 py-12 sm:px-6 sm:py-16 lg:space-y-20 lg:px-8">
    @if($restaurant->overview || count($restaurant->cuisines ?? []) || $restaurant->opening_time || $restaurant->breakfast_start_time || $restaurant->lunch_start_time || $restaurant->dinner_start_time)
    <div class="grid grid-cols-1 items-start gap-8 lg:grid-cols-3 lg:gap-12">
        <section class="min-w-0 lg:col-span-2" aria-labelledby="dining-overview">
            <p class="section-label mb-3">The dining experience</p>
            <h2 id="dining-overview" class="text-2xl font-bold text-navy sm:text-3xl">At our table</h2>
            @if($restaurant->overview)
            <p class="mt-6 whitespace-pre-line break-words text-sm leading-7 text-gray-600 sm:text-base">{{ $restaurant->overview }}</p>
            @endif
            @if(count($restaurant->cuisines ?? []))
            <h3 class="mt-8 text-sm font-semibold text-navy">Our cuisines</h3>
            <ul class="mt-3 flex flex-wrap gap-2">
                @foreach($restaurant->cuisines as $cuisine)
                <li class="max-w-full break-words rounded-full border border-gold/25 bg-gold/5 px-4 py-2 text-sm text-navy">{{ $cuisine }}</li>
                @endforeach
            </ul>
            @endif
        </section>
        @if($restaurant->opening_time || $restaurant->breakfast_start_time || $restaurant->lunch_start_time || $restaurant->dinner_start_time)
        <aside class="min-w-0 rounded-2xl border border-gold/20 bg-navy p-6 sm:p-8" aria-labelledby="dining-hours">
            <h2 id="dining-hours" class="text-xl font-semibold text-white">Hours & meal timings</h2>
            <dl class="mt-5 divide-y divide-white/10">
                @foreach(['Opening hours' => ['opening', 'closing'], 'Breakfast' => ['breakfast_start', 'breakfast_end'], 'Lunch' => ['lunch_start', 'lunch_end'], 'Dinner' => ['dinner_start', 'dinner_end']] as $label => [$start, $end])
                    @if($restaurant->{$start . '_time'} && $restaurant->{$end . '_time'})
                    @php
                        $from = substr($restaurant->{$start . '_time'}, 0, 5);
                        $to = substr($restaurant->{$end . '_time'}, 0, 5);
                    @endphp
                    <div class="py-4 first:pt-0">
                        <dt class="text-xs font-medium uppercase tracking-wider text-gold-light">{{ $label }}</dt>
                        <dd class="mt-2 text-sm text-white">
                            <time datetime="{{ $from }}">{{ \Carbon\Carbon::createFromFormat('H:i', $from)->format('g:i A') }}</time>
                            <span class="text-gray-400">–</span>
                            <time datetime="{{ $to }}">{{ \Carbon\Carbon::createFromFormat('H:i', $to)->format('g:i A') }}</time>
                            @if($to <= $from)<span class="mt-1 block text-xs text-gray-400">{{ $to === $from ? '24 hours' : 'Closes the following day' }}</span>@endif
                        </dd>
                    </div>
                    @endif
                @endforeach
            </dl>
            <p class="mt-3 text-xs text-gray-400">All times are local hotel time.</p>
        </aside>
        @endif
    </div>
    @endif

    @if(count($restaurant->featured_dishes ?? []))
    <section aria-labelledby="featured-dishes">
        <p class="section-label mb-3">From the kitchen</p>
        <h2 id="featured-dishes" class="text-2xl font-bold text-navy sm:text-3xl">Special & featured dishes</h2>
        <ul class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($restaurant->featured_dishes as $dish)
            <li class="flex min-w-0 items-start gap-4 rounded-xl border border-gold/20 bg-white p-6">
                <span aria-hidden="true" class="text-lg text-gold">✧</span>
                <p class="min-w-0 break-words text-sm font-medium leading-relaxed text-navy">{{ $dish }}</p>
            </li>
            @endforeach
        </ul>
    </section>
    @endif

    @if($restaurant->menu_image)
    <section id="food-menu" class="scroll-mt-24" aria-labelledby="food-menu-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="section-label mb-3">Explore our offerings</p><h2 id="food-menu-title" class="text-2xl font-bold text-navy sm:text-3xl">Food menu</h2></div>
            <a href="{{ Storage::disk('public')->url($restaurant->menu_image) }}" target="_blank" rel="noopener" class="rounded-lg border border-gold/40 px-4 py-3 text-sm font-semibold text-navy hover:bg-gold/10 focus-visible:outline-2 focus-visible:outline-gold">Open full-size menu ↗<span class="sr-only"> (opens in a new tab)</span></a>
        </div>
        <a href="{{ Storage::disk('public')->url($restaurant->menu_image) }}" target="_blank" rel="noopener" class="mt-6 block overflow-hidden rounded-2xl border border-gold/20 bg-white p-2 focus-visible:outline-2 focus-visible:outline-gold sm:p-5" aria-label="Open {{ $restaurant->name }} food menu at full size in a new tab">
            <img src="{{ Storage::disk('public')->url($restaurant->menu_image) }}" alt="Food menu at {{ $restaurant->name }}" class="mx-auto h-auto w-full max-w-4xl" loading="lazy">
        </a>
    </section>
    @endif

    @if($restaurant->images->isNotEmpty())
    <section aria-labelledby="restaurant-gallery-title">
        <p class="section-label mb-3">A closer look</p>
        <h2 id="restaurant-gallery-title" class="mb-6 text-2xl font-bold text-navy sm:text-3xl">Restaurant gallery</h2>
        @php($photos = $restaurant->images->map(fn ($image) => ['url' => Storage::disk('public')->url($image->image_path), 'caption' => $image->caption ?: $restaurant->name]))
        @include('frontend.partials.detail-photos', ['kind' => 'restaurant', 'photos' => $photos])
        @include('frontend.partials.detail-photo-viewer')
    </section>
    @endif
</div>
@else
<div class="mx-auto max-w-3xl px-4 py-16 text-center text-gray-600">
    <p>Restaurant details will be available soon.</p>
    <a href="{{ route('contact') }}" class="mt-5 inline-block font-semibold text-navy underline decoration-gold underline-offset-4">Contact us for dining enquiries</a>
</div>
@endif
@endsection
