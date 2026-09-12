@extends('layouts.admin')
@section('title','Services')
@section('page_title','Services & Add-ons')
@section('breadcrumb','Admin / Services')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-white">Services ({{ $services->count() }})</h2>
    <a href="{{ route('admin.services.create') }}" class="px-4 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white rounded-xl transition-colors">+ Add</a>
</div>

<p class="mb-2 text-xs text-gray-400 lg:hidden">Scroll sideways to see all columns and actions.</p>
<div role="region" aria-label="Services table" tabindex="0" class="rounded-2xl bg-white/5 border border-white/8 overflow-x-auto focus-visible:outline-2 focus-visible:outline-violet-400">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/8 text-xs text-gray-400 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Icon</th>
                <th class="px-5 py-3 text-left">Title</th>
                <th class="px-5 py-3 text-left">Price Label</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($services as $service)
            <tr class="hover:bg-white/3 transition-colors">
                <td class="px-5 py-3.5 text-2xl">{{ $service->icon }}</td>
                <td class="px-5 py-3.5">
                    <p class="font-medium text-white">{{ $service->title }}</p>
                    <p class="text-xs text-gray-500 mt-0.5 truncate max-w-xs">{{ $service->description }}</p>
                </td>
                <td class="px-5 py-3.5 text-amber-400">{{ $service->price_label }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.services.toggle-status', $service) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 {{ $service->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20' }}">
                            {{ $service->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.services.edit', $service) }}" class="px-3 py-1.5 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">Edit</a>
                        <form method="POST" action="{{ route('admin.services.destroy', $service) }}" onsubmit="return confirm('Delete?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No services yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
