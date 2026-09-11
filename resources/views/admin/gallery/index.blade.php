@extends('layouts.admin')
@section('title','Gallery')
@section('page_title','Photo & Video Gallery')
@section('breadcrumb','Admin / Gallery')

@section('content')

{{-- Upload form --}}
<div class="mb-8 p-6 rounded-2xl bg-white/5 border border-white/8">
    <h3 class="text-sm font-semibold text-gray-200 mb-4">Upload Photos & Videos</h3>
    <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-admin.field label="Caption / Title" name="title" hint="Optional" />
            <x-admin.field label="Badge Label" name="badge_label" hint="e.g. Rooms, Dining" />
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Section</label>
                <select name="section" class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 transition-all">
                    @foreach(['general','rooms','dining','services','location'] as $s)
                        <option value="{{ $s }}" class="bg-gray-900">{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-white/15 rounded-xl cursor-pointer hover:border-violet-400/50 transition-colors">
            <span class="text-2xl mb-1">🖼️</span>
            <span class="text-sm text-gray-400">Select photos or videos (multiple allowed)</span>
            <span class="mt-1 text-xs text-gray-500">Images, MP4 or WEBM · Max 50 MB per file</span>
            <input type="file" name="images[]" class="hidden" accept="image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,video/mp4,video/webm" multiple required>
        </label>
        <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">Upload Media</button>
    </form>
</div>

{{-- Gallery grid --}}
<h3 class="text-sm font-semibold text-gray-200 mb-4">All Media ({{ $items->count() }})</h3>
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
    @forelse($items as $item)
    <div class="group relative rounded-xl overflow-hidden bg-white/5 border border-white/8">
        @if($item->media_type === 'video')
        <video src="{{ $item->image_url }}" controls playsinline preload="metadata" class="w-full h-36 object-cover" aria-label="{{ $item->title ?: 'Gallery video' }}"></video>
        @else
        <img src="{{ $item->image_url }}" alt="{{ $item->title }}"
             class="w-full h-36 object-cover transition-transform group-hover:scale-105 duration-300"
             onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'100\' height=\'100\'><rect fill=\'%23374151\' width=\'100\' height=\'100\'/><text fill=\'%236B7280\' x=\'50\' y=\'55\' text-anchor=\'middle\' font-size=\'30\'>🖼</text></svg>'">
        @endif
        <div class="p-3">
            <p class="text-xs font-medium text-gray-300 truncate">{{ $item->title ?: 'Untitled' }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ ucfirst($item->section) }}</p>
            <div class="flex items-center gap-1.5 mt-2">
                {{-- Status toggle --}}
                <form method="POST" action="{{ route('admin.gallery.toggle-status', $item) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-2 py-0.5 rounded-full ring-1 {{ $item->is_active ? 'text-emerald-400 ring-emerald-400/20 bg-emerald-500/10' : 'text-gray-500 ring-gray-500/20 bg-gray-500/10' }}">
                        {{ $item->is_active ? '✓' : '–' }}
                    </button>
                </form>
                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" onsubmit="return confirm('Delete this gallery item?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-2 py-0.5 rounded-full text-red-400 ring-1 ring-red-400/20 bg-red-500/10 hover:bg-red-500/20">✕</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16 text-gray-500">No photos or videos uploaded yet.</div>
    @endforelse
</div>

@endsection
