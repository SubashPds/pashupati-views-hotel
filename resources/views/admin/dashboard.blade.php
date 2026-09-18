@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('content')

{{-- Welcome --}}
<div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-violet-600/20 to-purple-500/10 border border-violet-500/20">
    <h2 class="text-lg font-semibold text-white">Welcome back, {{ auth()->user()->name }}! 👋</h2>
    <p class="text-sm text-gray-400 mt-1">Use the sidebar to access the areas available to your role. Contact a superadmin if you need additional access.</p>
    <a href="{{ route('home') }}" class="mt-3 inline-block text-sm text-violet-300 hover:underline">View hotel website →</a>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">
    @if(auth()->user()->hasPermission('rooms'))
    <div class="p-5 rounded-2xl bg-gradient-to-br from-violet-600/20 to-violet-500/10 border border-violet-500/20 hover:-translate-y-1 transition-transform">
        <div class="text-3xl mb-3">🛏️</div>
        <div class="text-2xl font-bold text-white">{{ $stats['rooms'] }}</div>
        <div class="text-xs text-gray-400 mt-1">Total Rooms</div>
    </div>
    @endif
    @if(auth()->user()->hasPermission('enquiries'))
    <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-600/20 to-amber-500/10 border border-amber-500/20 hover:-translate-y-1 transition-transform">
        <div class="text-3xl mb-3">📩</div>
        <div class="text-2xl font-bold text-white">{{ $stats['enquiries'] }}</div>
        <div class="text-xs text-gray-400 mt-1">New Enquiries</div>
    </div>
    @endif
    @if(auth()->user()->hasPermission('testimonials'))
    <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-600/20 to-emerald-500/10 border border-emerald-500/20 hover:-translate-y-1 transition-transform">
        <div class="text-3xl mb-3">💬</div>
        <div class="text-2xl font-bold text-white">{{ $stats['testimonials'] }}</div>
        <div class="text-xs text-gray-400 mt-1">Testimonials</div>
    </div>
    @endif
    @if(auth()->user()->hasPermission('settings'))
    <a href="{{ route('admin.settings.index') }}"
       class="p-5 rounded-2xl bg-gradient-to-br from-sky-600/20 to-sky-500/10 border border-sky-500/20 hover:-translate-y-1 transition-transform block">
        <div class="text-3xl mb-3">⚙️</div>
        <div class="text-2xl font-bold text-white">Edit</div>
        <div class="text-xs text-gray-400 mt-1">Site Settings</div>
    </a>
    @endif
</div>

{{-- Quick Links --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-10">
    @foreach([
        ['route'=>'admin.rooms.index','icon'=>'🛏️','label'=>'Rooms'],
        ['route'=>'admin.experiences.index','icon'=>'✨','label'=>'Experiences'],
        ['route'=>'admin.gallery.index','icon'=>'🖼️','label'=>'Gallery'],
        ['route'=>'admin.services.index','icon'=>'🛎️','label'=>'Services'],
        ['route'=>'admin.testimonials.index','icon'=>'💬','label'=>'Testimonials'],
        ['route'=>'admin.enquiries.index','icon'=>'📩','label'=>'Enquiries'],
    ] as $link)
    @continue(!auth()->user()->canAccessAdminRoute($link['route']))
    <a href="{{ route($link['route']) }}"
       class="flex flex-col items-center gap-2 p-4 rounded-xl bg-white/5 border border-white/8 hover:bg-white/10 hover:-translate-y-0.5 transition-all text-center">
        <span class="text-2xl">{{ $link['icon'] }}</span>
        <span class="text-xs font-medium text-gray-300">{{ $link['label'] }}</span>
    </a>
    @endforeach
</div>

{{-- Recent Enquiries --}}
@if(auth()->user()->hasPermission('enquiries'))
<div class="rounded-2xl bg-white/5 border border-white/8 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-white/8">
        <h3 class="text-sm font-semibold text-gray-200">Recent Enquiries</h3>
        <a href="{{ route('admin.enquiries.index') }}" class="text-xs text-violet-400 hover:text-violet-300">View all →</a>
    </div>
    <div class="divide-y divide-white/5">
        @forelse($recentEnquiries as $enquiry)
        <div class="flex items-center justify-between px-5 py-3.5">
            <div>
                <p class="text-sm font-medium text-gray-200">{{ $enquiry->guest_name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ $enquiry->category }} · {{ $enquiry->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex text-xs font-medium px-2 py-0.5 rounded-full ring-1 {{ $enquiry->status_badge_color }}">
                    {{ ucfirst($enquiry->status) }}
                </span>
                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="text-xs text-gray-400 hover:text-white transition-colors">View →</a>
            </div>
        </div>
        @empty
        <p class="px-5 py-8 text-sm text-gray-500 text-center">No enquiries yet.</p>
        @endforelse
    </div>
</div>
@endif

@endsection
