@extends('layouts.admin')
@section('title','Testimonials')
@section('page_title','Testimonials')
@section('breadcrumb','Admin / Testimonials')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-white">Testimonials ({{ $testimonials->count() }})</h2>
    <a href="{{ route('admin.testimonials.create') }}" class="px-4 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white rounded-xl transition-colors">+ Add</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    @forelse($testimonials as $t)
    <div class="p-5 rounded-2xl bg-white/5 border border-white/8">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="font-semibold text-white">{{ $t->author_name }}</p>
                <p class="text-xs text-gray-500">{{ $t->author_date }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-amber-400 text-sm">{{ str_repeat('★', $t->rating) }}</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-gray-500/15 text-gray-400 ring-1 ring-gray-400/20">{{ $t->tag }}</span>
            </div>
        </div>
        <p class="text-sm text-gray-400 leading-relaxed mb-4">"{{ $t->review }}"</p>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.testimonials.toggle-status', $t) }}">
                @csrf @method('PATCH')
                <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 {{ $t->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20' }}">
                    {{ $t->is_active ? 'Active' : 'Inactive' }}
                </button>
            </form>
            <a href="{{ route('admin.testimonials.edit', $t) }}" class="px-3 py-1 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">Edit</a>
            <form method="POST" action="{{ route('admin.testimonials.destroy', $t) }}" onsubmit="return confirm('Delete?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-3 py-1 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">Delete</button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16 text-gray-500">No testimonials yet.</div>
    @endforelse
</div>
@endsection
