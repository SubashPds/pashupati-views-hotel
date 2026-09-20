@extends('layouts.admin')
@section('title', 'Enquiry from '.$enquiry->guest_name)
@section('page_title', 'View Enquiry')
@section('breadcrumb')
    <a href="{{ route('admin.enquiries.index') }}" class="hover:text-gray-300">Enquiries</a> / {{ $enquiry->guest_name }}
@endsection

@section('content')
<div class="max-w-2xl">
    <div class="p-6 rounded-2xl bg-white/5 border border-white/8 mb-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <h2 class="text-lg font-semibold text-white">{{ $enquiry->guest_name }}</h2>
                <x-admin.enquiry-time :date="$enquiry->created_at" class="mt-0.5" />
            </div>
            <span class="inline-flex text-sm font-medium px-3 py-1 rounded-full ring-1 {{ $enquiry->status_badge_color }}">
                {{ ucfirst($enquiry->status) }}
            </span>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Email</p>
                <p class="text-sm text-white">{{ $enquiry->email ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Phone</p>
                <p class="text-sm text-white">{{ $enquiry->phone ?: '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Category</p>
                <p class="text-sm text-white">{{ $enquiry->category ?: '—' }}</p>
            </div>
        </div>

        <div>
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Message</p>
            <div class="p-4 bg-white/5 rounded-xl text-sm text-gray-300 leading-relaxed">
                {{ $enquiry->message ?: 'No message provided.' }}
            </div>
        </div>
    </div>

    {{-- Status change --}}
    <div class="p-5 rounded-2xl bg-white/5 border border-white/8">
        <p class="text-sm font-medium text-gray-200 mb-3">Update Status</p>
        <form method="POST" action="{{ route('admin.enquiries.update-status', $enquiry) }}" class="flex items-center gap-3">
            @csrf @method('PATCH')
            <select name="status" class="flex-1 px-4 py-2.5 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 transition-all">
                @foreach(['new','read','replied','archived'] as $s)
                    <option value="{{ $s }}" {{ $enquiry->status === $s ? 'selected' : '' }} class="bg-gray-900">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-5 py-2.5 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">Save</button>
        </form>
    </div>
</div>
@endsection
