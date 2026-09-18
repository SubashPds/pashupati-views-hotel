@extends('layouts.auth')

@section('title', 'Sign In')

@section('content')

<div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl shadow-black/40">

    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-purple-500 text-3xl shadow-lg shadow-violet-900/50 mb-4">
            🏨
        </div>
        <h1 class="text-xl font-bold text-white">Pashupati Views Hotel</h1>
        <p class="text-sm text-gray-400 mt-1">Account sign in</p>
    </div>

    {{-- Error alert --}}
    @if ($errors->any())
        <div class="flex items-start gap-3 mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm">
            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"></path>
            </svg>
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="admin@pashupativiews.com"
                required
                autofocus
                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 text-sm
                       focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
            >
        </div>

        {{-- Password --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="••••••••••"
                required
                class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 text-sm
                       focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
            >
        </div>

        {{-- Remember me --}}
        <div class="flex items-center gap-2">
            <input type="checkbox" id="remember" name="remember"
                   class="w-4 h-4 accent-violet-500 rounded cursor-pointer">
            <label for="remember" class="text-sm text-gray-400 cursor-pointer select-none">Remember me</label>
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            id="btn-login"
            class="w-full py-3 bg-gradient-to-r from-violet-600 to-purple-500 hover:from-violet-500 hover:to-purple-400
                   text-white text-sm font-semibold rounded-xl shadow-lg shadow-violet-900/40
                   hover:shadow-violet-900/60 hover:-translate-y-0.5 active:translate-y-0
                   transition-all duration-150 cursor-pointer"
        >
            Sign In
        </button>
    </form>

    <p class="text-center text-xs text-gray-600 mt-6">
        Access restricted to authorised personnel only.
    </p>
</div>

@endsection
