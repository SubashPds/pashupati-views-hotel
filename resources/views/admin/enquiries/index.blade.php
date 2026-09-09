@extends('layouts.admin')
@section('title','Enquiries')
@section('page_title','Enquiries')
@section('breadcrumb','Admin / Enquiries')

@section('content')

{{-- Filter --}}
<div class="flex items-center gap-3 mb-6 flex-wrap">
    @foreach(['all','new','read','replied','archived'] as $status)
    <a href="{{ route('admin.enquiries.index', $status !== 'all' ? ['status' => $status] : []) }}"
       class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors
              {{ request('status', 'all') === $status ? 'bg-violet-600 border-violet-600 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white' }}">
        {{ ucfirst($status) }}
    </a>
    @endforeach
</div>

<div class="rounded-2xl bg-white/5 border border-white/8 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-white/8 text-xs text-gray-400 uppercase tracking-wide">
                <th class="px-5 py-3 text-left">Guest</th>
                <th class="px-5 py-3 text-left">Category</th>
                <th class="px-5 py-3 text-left">Contact</th>
                <th class="px-5 py-3 text-left">Date</th>
                <th class="px-5 py-3 text-left">Status</th>
                <th class="px-5 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-white/5">
            @forelse($enquiries as $enquiry)
            <tr class="hover:bg-white/3 transition-colors {{ $enquiry->status === 'new' ? 'font-semibold' : '' }}">
                <td class="px-5 py-3.5">
                    <p class="text-white {{ $enquiry->status === 'new' ? 'font-semibold' : 'font-medium' }}">
                        {{ $enquiry->status === 'new' ? '🔴 ' : '' }}{{ $enquiry->guest_name }}
                    </p>
                </td>
                <td class="px-5 py-3.5 text-gray-400">{{ $enquiry->category ?? '—' }}</td>
                <td class="px-5 py-3.5 text-gray-400">
                    {{ $enquiry->email ?? $enquiry->phone ?? '—' }}
                </td>
                <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $enquiry->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3.5">
                    <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full ring-1 {{ $enquiry->status_badge_color }}">
                        {{ ucfirst($enquiry->status) }}
                    </span>
                </td>
                <td class="px-5 py-3.5 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="px-3 py-1.5 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">View</a>
                        <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" onsubmit="return confirm('Delete enquiry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">Delete</button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No enquiries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-5">{{ $enquiries->withQueryString()->links() }}</div>

@endsection
