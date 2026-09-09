@extends('layouts.admin')
@section('title','Rooms')
@section('page_title','Rooms')
@section('breadcrumb','Admin / Rooms')

@section('content')

<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-white">All Rooms ({{ $rooms->count() }})</h2>
    <a href="{{ route('admin.rooms.create') }}"
       class="px-4 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white rounded-xl transition-colors">
        + Add Room
    </a>
</div>

<div class="rounded-2xl bg-white/5 border border-white/8 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/8 text-xs text-gray-400 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Order</th>
                <th class="px-5 py-3 text-left">Room</th>
                <th class="px-5 py-3 text-left">Category</th>
                <th class="px-5 py-3 text-left">Price/Night</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($rooms as $room)
            <tr class="hover:bg-white/3 transition-colors">
                <td class="px-5 py-3.5 text-gray-400">{{ $room->sort_order }}</td>
                <td class="px-5 py-3.5">
                    <p class="font-medium text-white">{{ $room->name }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $room->bed_type }} · {{ $room->max_guests }} guests · {{ $room->size_sqm }}m²</p>
                </td>
                <td class="px-5 py-3.5">
                    <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-violet-600/15 text-violet-300 ring-1 ring-violet-400/20">
                        {{ ucfirst($room->category) }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-amber-400 font-medium">{{ $room->formatted_price }}</td>
                <td class="px-5 py-3.5">
                    <form method="POST" action="{{ route('admin.rooms.toggle-status', $room) }}">
                        @csrf @method('PATCH')
                        <button type="submit"
                                class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 transition-colors
                                       {{ $room->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20 hover:bg-emerald-500/25' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20 hover:bg-gray-500/25' }}">
                            {{ $room->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.rooms.edit', $room) }}"
                           class="px-3 py-1.5 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.rooms.destroy', $room) }}"
                              onsubmit="return confirm('Delete this room?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No rooms yet. <a href="{{ route('admin.rooms.create') }}" class="text-violet-400">Add one →</a></td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
