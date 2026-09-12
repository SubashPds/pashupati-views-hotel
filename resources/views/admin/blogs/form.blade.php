@extends('layouts.admin')
@section('title', $blog->exists ? 'Edit Blog' : 'Add Blog')
@section('page_title', $blog->exists ? 'Edit Blog' : 'Add Blog')
@section('breadcrumb', 'Admin / Blogs / ' . ($blog->exists ? 'Edit' : 'Add'))
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $blog->exists ? route('admin.blogs.update', $blog) : route('admin.blogs.store') }}" class="max-w-4xl rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-8 space-y-6">
    @csrf
    @if($blog->exists) @method('PUT') @endif
    <x-admin.field label="Title" name="title" :value="$blog->title" required />
    <x-admin.field label="Short summary" name="excerpt" type="textarea" :value="$blog->excerpt" hint="Up to 500 characters. Leave blank to use the beginning of the article." />
    <div>
        <label for="content" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Article <span class="text-red-400">*</span></label>
        <textarea id="content" name="content" rows="16" required maxlength="100000" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm leading-relaxed text-white focus:border-violet-500 focus:outline-none">{{ old('content', $blog->content) }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Write your article as plain text. Line breaks and paragraphs are preserved.</p>
        @error('content')<p class="mt-1 text-xs text-red-400">{{ $message }}</p>@enderror
    </div>
    <div data-blog-cover data-saved-url="{{ $blog->cover_url }}" class="space-y-3">
        <label for="cover_image" class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Cover image</label>
        <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-400 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-white">
        <p class="text-xs text-gray-500">JPG, PNG, or WebP, up to 4 MB. Selected images appear below.</p>
        <div data-cover-preview class="{{ $blog->cover_url ? '' : 'hidden' }}">
            <img data-cover-image @if($blog->cover_url) src="{{ $blog->cover_url }}" @endif alt="Cover image preview" class="max-h-64 max-w-full rounded-xl object-contain">
        </div>
        <button type="button" data-clear-cover class="hidden rounded-lg bg-white/10 px-3 py-2 text-xs text-gray-300 hover:bg-white/15">Clear selected image</button>
        @if($blog->cover_image)
            <label class="flex items-center gap-2 text-sm text-gray-400"><input type="checkbox" name="remove_cover" value="1" @checked(old('remove_cover'))> Remove saved cover image</label>
        @endif
        @error('cover_image')<p class="text-xs text-red-400">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="is_published" class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Status</label>
        <select name="is_published" id="is_published" class="w-full rounded-xl border border-white/10 bg-gray-900 px-4 py-3 text-sm text-white">
            <option value="0" @selected(!old('is_published', $blog->is_published))>Draft — only visible in admin</option>
            <option value="1" @selected(old('is_published', $blog->is_published))>Published — visible on the website</option>
        </select>
    </div>
    <div class="flex gap-3 border-t border-white/8 pt-6">
        <button type="submit" class="rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white hover:bg-violet-500">{{ $blog->exists ? 'Save Changes' : 'Create Blog' }}</button>
        <a href="{{ route('admin.blogs.index') }}" class="rounded-xl bg-white/8 px-6 py-3 text-sm text-gray-300 hover:bg-white/15">Cancel</a>
    </div>
</form>
@endsection
