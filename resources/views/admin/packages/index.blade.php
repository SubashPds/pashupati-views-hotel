@extends('layouts.admin')
@section('title','Packages')
@section('page_title','Stay Packages')
@section('breadcrumb','Admin / Packages')

@section('content')

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-400">{{ $packages->count() }} package(s) total</p>
    <a href="{{ route('admin.packages.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
        + New Package
    </a>
</div>

<div class="space-y-3">
    @forelse($packages as $pkg)
    <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 border border-white/8 hover:border-white/12 transition-colors">

        {{-- Cover --}}
        <div class="w-20 h-16 rounded-xl overflow-hidden bg-white/5 shrink-0 flex items-center justify-center text-2xl">
            @if($pkg->cover_image_url)
                <img src="{{ $pkg->cover_image_url }}" alt="{{ $pkg->name }}" class="w-full h-full object-cover">
            @else
                🎁
            @endif
        </div>

        {{-- Detail --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <p class="text-sm font-semibold text-white">{{ $pkg->name }}</p>
                @if($pkg->badge)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-500/15 text-amber-400 ring-1 ring-amber-400/20">
                        {{ $pkg->badge }}
                    </span>
                @endif
                <span class="px-2 py-0.5 text-xs rounded-full font-medium
                      {{ $pkg->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-400/20'
                                         : 'bg-gray-500/15 text-gray-400 ring-1 ring-gray-400/20' }}">
                    {{ $pkg->is_active ? 'Active' : 'Hidden' }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5">
                {{ $pkg->duration ?? '—' }}
                @if($pkg->price_label) · {{ $pkg->price_label }} @endif
            </p>
            <p class="text-xs text-gray-500 mt-1 line-clamp-1">{{ $pkg->short_description }}</p>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.packages.edit', $pkg) }}"
               class="px-3 py-1.5 text-xs font-medium text-gray-300 bg-white/5 hover:bg-white/10 rounded-lg transition-colors">
                Edit
            </a>
            <form method="POST" action="{{ route('admin.packages.toggle-status', $pkg) }}">
                @csrf @method('PATCH')
                <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                               {{ $pkg->is_active ? 'text-amber-400 bg-amber-500/10 hover:bg-amber-500/20'
                                                  : 'text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20' }}">
                    {{ $pkg->is_active ? 'Hide' : 'Show' }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.packages.destroy', $pkg) }}"
                  onsubmit="return confirm('Delete package: {{ addslashes($pkg->name) }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-3 py-1.5 text-xs font-medium text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg transition-colors">
                    Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <p class="text-4xl mb-3">🎁</p>
        <p class="text-sm">No packages yet. <a href="{{ route('admin.packages.create') }}" class="text-violet-400 underline">Create one →</a></p>
    </div>
    @endforelse
</div>

@endsection
