@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('breadcrumb', 'Admin / Dashboard')

@section('content')

{{-- Welcome banner --}}
<div class="mb-8 p-6 rounded-2xl bg-gradient-to-r from-violet-600/20 to-purple-500/10 border border-violet-500/20">
    <h2 class="text-lg font-semibold text-white">
        Welcome back, {{ auth()->user()->name }}! 👋
    </h2>
    <p class="text-sm text-gray-400 mt-1">
        You are signed in as Super Administrator. Manage your hotel from here.
    </p>
</div>

{{-- Stats grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-10">

    @php
        $stats = [
            ['icon' => '🛏️', 'label' => 'Total Rooms',    'value' => '0',      'color' => 'from-violet-600/20 to-violet-500/10 border-violet-500/20'],
            ['icon' => '📅', 'label' => 'Bookings Today', 'value' => '0',      'color' => 'from-amber-600/20 to-amber-500/10 border-amber-500/20'],
            ['icon' => '👥', 'label' => 'Guests',         'value' => '0',      'color' => 'from-emerald-600/20 to-emerald-500/10 border-emerald-500/20'],
            ['icon' => '💰', 'label' => 'Revenue',        'value' => 'NPR 0',  'color' => 'from-sky-600/20 to-sky-500/10 border-sky-500/20'],
        ];
    @endphp

    @foreach($stats as $stat)
        <div class="p-5 rounded-2xl bg-gradient-to-br {{ $stat['color'] }} border hover:-translate-y-1 transition-transform duration-200">
            <div class="text-3xl mb-3">{{ $stat['icon'] }}</div>
            <div class="text-2xl font-bold text-white">{{ $stat['value'] }}</div>
            <div class="text-xs text-gray-400 mt-1">{{ $stat['label'] }}</div>
        </div>
    @endforeach

</div>

{{-- Placeholder section --}}
<div class="rounded-2xl bg-white/5 border border-white/8 p-6">
    <h3 class="text-sm font-semibold text-gray-300 mb-4">Recent Activity</h3>
    <p class="text-sm text-gray-500 text-center py-10">No activity yet. Start adding content through the CMS.</p>
</div>

@endsection
