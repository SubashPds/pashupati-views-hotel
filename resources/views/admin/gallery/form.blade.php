@extends('layouts.admin')
@section('title', 'Edit Gallery Item')
@section('page_title', 'Edit Gallery Item')
@section('breadcrumb', 'Admin / Gallery / Edit')
@section('content')
<form method="POST" action="{{ route('admin.gallery.update', $galleryItem) }}" class="max-w-2xl space-y-5 rounded-2xl border border-white/8 bg-white/5 p-6">
    @csrf @method('PUT')
    @if($galleryItem->media_type === 'video')
        <video src="{{ $galleryItem->image_url }}" controls playsinline preload="metadata" aria-label="Current gallery video" class="max-h-64 w-full rounded-xl bg-black/20"></video>
    @else
        <img src="{{ $galleryItem->image_url }}" alt="{{ $galleryItem->title ?: 'Current gallery image' }}" class="max-h-64 w-full rounded-xl bg-black/20 object-contain">
    @endif
    <x-admin.field label="Caption / Title" name="title" :value="$galleryItem->title" maxlength="255" />
    <x-admin.field label="Badge Label" name="badge_label" :value="$galleryItem->badge_label" maxlength="100" />
    <div>
        <label for="section" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Section</label>
        <select id="section" name="section" class="w-full rounded-xl border border-white/10 bg-gray-900 px-4 py-3 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-500">
            @foreach(collect(['general','rooms','dining','services','location', $galleryItem->section])->filter()->unique() as $section)
                <option value="{{ $section }}" @selected(old('section', $galleryItem->section ?? 'general') === $section)>{{ ucfirst($section) }}</option>
            @endforeach
        </select>
    </div>
    <x-admin.field label="Sort Order" name="sort_order" type="number" :value="$galleryItem->sort_order" min="0" required />
    <div class="flex gap-3 pt-2">
        <button type="submit" class="rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white hover:bg-violet-500">Save Changes</button>
        <a href="{{ route('admin.gallery.index') }}" class="rounded-xl bg-white/8 px-5 py-3 text-sm text-gray-300 hover:bg-white/15">Cancel</a>
    </div>
</form>
@endsection
