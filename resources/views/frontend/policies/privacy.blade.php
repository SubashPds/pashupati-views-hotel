@extends('layouts.app')
@section('title', 'Privacy Policy — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('meta_description', 'Read Pashupati Views Hotel\'s Privacy Policy to understand how we handle your data.')

@section('content')

{{-- ── Hero Section ──────────────────────────────────────────────────── --}}
<div class="relative overflow-hidden bg-gradient-to-br from-[#0d1b2a] to-[#1a2d42] py-16 sm:py-24">
    {{-- Decorative orbs --}}
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[image:radial-gradient(circle,rgba(184,149,59,0.1)_0%,transparent_70%)]"></div>
    <div class="absolute -bottom-32 -left-32 w-80 h-80 rounded-full bg-[image:radial-gradient(circle,rgba(184,149,59,0.08)_0%,transparent_70%)]"></div>

    <div class="relative max-w-4xl mx-auto px-6 sm:px-10 lg:px-8">
        {{-- Breadcrumb --}}
        <nav aria-label="Breadcrumb" class="mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-[#b8953b] hover:text-[#d4af5b] transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Home
            </a>
            <span class="text-gray-600 mx-3">/</span>
            <span class="text-gray-400">Privacy Policy</span>
        </nav>

        {{-- Heading --}}
        <h1 class="text-4xl sm:text-5xl font-bold tracking-tight leading-[1.15] text-white mb-4">
            Privacy Policy
        </h1>
        <p class="text-lg text-gray-300">Effective as of September 20, 2026</p>
    </div>
</div>

{{-- ── Content Section ──────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-b from-[#faf8f3] to-white py-16 sm:py-24">
    <div class="max-w-4xl mx-auto px-6 sm:px-10 lg:px-8">
        @if($policy && $policy->is_active)
            <div class="prose prose-sm sm:prose max-w-none text-gray-700 leading-relaxed">
                {!! $policy->safeDescriptionHtml() !!}
            </div>

            {{-- Last Updated --}}
            <div class="mt-16 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    Last updated: {{ $policy->updated_at->format('F j, Y') }}
                </p>
            </div>
        @else
            <div class="text-center py-20">
                <p class="text-gray-500">This policy is currently not available.</p>
            </div>
        @endif
    </div>
</div>

{{-- ── CTA Section ──────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-b from-white to-[#faf8f3] py-16">
    <div class="max-w-4xl mx-auto px-6 sm:px-10 lg:px-8">
        <div class="rounded-2xl overflow-hidden flex flex-col sm:flex-row items-center gap-8 px-8 py-12 sm:py-14 bg-white border border-[rgba(184,149,59,0.15)] shadow-[0_4px_20px_rgba(184,149,59,0.08)]">
            <div class="flex-1 text-center sm:text-left">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] mb-2 text-[#b8953b]">Have Questions?</p>
                <p class="text-gray-900 font-bold text-2xl sm:text-3xl tracking-tight">We're Here to Help</p>
                <p class="text-gray-600 text-sm mt-2">Reach out if you have any questions about our privacy practices.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                <a href="#contact"
                    class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl transition-all duration-200 hover:shadow-lg hover:scale-105 bg-gradient-to-br from-[#d4af5b] to-[#b8953b] text-[#0d1b2a]">
                    Contact Us
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                @if(!empty($settings['contact_whatsapp']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                    target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 px-6 py-3.5 text-sm font-semibold rounded-xl border transition-all duration-200 hover:shadow-md border-[rgba(184,149,59,0.3)] text-[#0d1b2a]"
                    aria-label="WhatsApp">
                    WhatsApp
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4m-4-4l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
