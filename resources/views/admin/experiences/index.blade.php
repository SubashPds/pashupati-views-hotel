@extends('layouts.admin')
@section('title','Experiences')
@section('page_title','Experiences')
@section('breadcrumb','Admin / Experiences')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-white">Experiences ({{ $experiences->count() }})</h2>
    <a href="{{ route('admin.experiences.create') }}" class="px-4 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white rounded-xl transition-colors">+ Add</a>
</div>

<div class="rounded-2xl bg-white/5 border border-white/8 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/8 text-xs text-gray-400 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Order</th>
                <th class="px-5 py-3 text-left">Icon</th>
                <th class="px-5 py-3 text-left">Title</th>
                <th class="px-5 py-3 text-left">Description</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($experiences as $exp)
            <tr class="hover:bg-white/3 transition-colors">
                <td class="px-5 py-3.5 text-gray-400">{{ $exp->sort_order }}</td>
                <td class="px-5 py-3.5 text-2xl">{{ $exp->icon }}</td>
                <td class="px-5 py-3.5 font-medium text-white">{{ $exp->title }}</td>
                <td class="px-5 py-3.5 text-gray-400 max-w-xs truncate">{{ $exp->description }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.experiences.toggle-status', $exp) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 transition-colors {{ $exp->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20' }}">
                            {{ $exp->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.experiences.edit', $exp) }}" class="px-3 py-1.5 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">Edit</a>
                        <form method="POST" action="{{ route('admin.experiences.destroy', $exp) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No items yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
