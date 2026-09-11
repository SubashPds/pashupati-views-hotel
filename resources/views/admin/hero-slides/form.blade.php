@extends('layouts.admin')
@section('title', $slide->exists ? 'Edit Slide' : 'Add Slide')
@section('page_title', $slide->exists ? 'Edit Slide' : 'Add Slide')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $slide->exists ? route('admin.hero-slides.update', $slide) : route('admin.hero-slides.store') }}" class="max-w-xl p-6 rounded-2xl bg-white/5 space-y-5">
    @csrf
    @if($slide->exists) @method('PUT') @endif
    <x-admin.field label="Title / media description" name="title" :value="$slide->title" required />
    <div>
        <label for="media" class="block mb-2 text-sm text-gray-300">Image or video</label>
        <input id="media" name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/webm" @required(!$slide->exists) class="w-full text-sm text-gray-300">
        <p class="text-xs text-gray-400 mt-2">JPG, PNG, WebP, MP4 or WebM. Maximum 50 MB. Use landscape media. Videos play muted. {{ $slide->exists ? 'Leave empty to keep current media.' : '' }}</p>
    </div>
    @if($slide->exists)
        @if($slide->media_type === 'video')
            <video src="{{ $slide->media_url }}" controls preload="metadata" class="w-full rounded-xl"></video>
        @else
            <img src="{{ $slide->media_url }}" alt="{{ $slide->title }}" class="w-full rounded-xl">
        @endif
    @endif
    <x-admin.field label="Sort order (lowest first)" name="sort_order" type="number" :value="$slide->sort_order ?? 0" required />
    <input type="hidden" name="is_active" value="0">
    <label class="flex gap-2 text-gray-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $slide->exists ? $slide->is_active : true))> Visible on home page</label>
    <div class="flex gap-4">
        <button class="px-6 py-3 bg-violet-600 text-white rounded-xl">Save slide</button>
        <a href="{{ route('admin.hero-slides.index') }}" class="px-6 py-3 text-gray-300">Cancel</a>
    </div>
</form>
@endsection
