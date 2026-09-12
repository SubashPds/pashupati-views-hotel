@extends('layouts.admin')
@section('title', 'Offers & Advertising')
@section('page_title', 'Offers & Advertising')
@section('content')
<div class="flex flex-wrap items-center justify-between mb-6 gap-4">
    <p class="text-sm text-gray-400 max-w-2xl">Active, unexpired cards appear in a stack once per browsing session. Closing the top card reveals the next. Lower sort orders appear on top. End dates use Nepal time.</p>
    <a href="{{ route('admin.promotions.create') }}" class="px-5 py-3 bg-violet-600 hover:bg-violet-500 text-white rounded-xl">Add promotion</a>
</div>
<div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
@forelse($promotions as $promotion)
    <article class="rounded-2xl overflow-hidden bg-white/5 border border-white/10">
        @if($promotion->image_url)
            <img src="{{ $promotion->image_url }}" alt="{{ $promotion->image_alt ?? '' }}" class="w-full h-48 object-contain bg-black/20">
        @endif
        <div class="p-5 space-y-3">
            <h2 class="text-white font-semibold break-words">{{ $promotion->title }}</h2>
            <p class="text-sm text-gray-400">Order {{ $promotion->sort_order }} · {{ $promotion->is_expired ? 'Expired' : ($promotion->is_active ? 'Active' : 'Hidden') }}</p>
            <p class="text-xs text-gray-400">{{ $promotion->end_date ? 'Ends '.$promotion->end_date->format('j M Y').' (Nepal time)' : 'No end date' }}</p>
            <p class="text-sm text-gray-400 break-words">{{ Str::limit($promotion->description, 120) }}</p>
            <div class="flex gap-4 items-center">
                <a href="{{ route('admin.promotions.edit', $promotion) }}" class="p-2 text-violet-300 hover:text-white">Edit</a>
                <form method="POST" action="{{ route('admin.promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion and its image?')">
                    @csrf @method('DELETE')
                    <button class="p-2 text-red-300 hover:text-white">Delete</button>
                </form>
            </div>
        </div>
    </article>
@empty
    <div class="md:col-span-2 p-8 rounded-2xl border border-dashed border-white/15 text-gray-400">No promotions yet. Add your first card to show an offer when visitors arrive.</div>
@endforelse
</div>
@endsection
