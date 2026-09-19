@extends('layouts.app')

@section('title', 'Contact Us — ' . ($settings['site_name'] ?? 'Pashupati Views Hotel'))
@section('meta_description', 'Get in touch with Pashupati Views Hotel for bookings, enquiries, and assistance.')

@section('content')

{{-- ── Hero Section ──────────────────────────────────────────────────── --}}
<div class="faq-hero relative overflow-hidden bg-gradient-to-br from-[#0d1b2a] to-[#1a2d42]">
    {{-- Decorative orbs --}}
    <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[image:radial-gradient(circle,rgba(184,149,59,0.1)_0%,transparent_70%)]"></div>
    <div class="absolute -bottom-32 -left-32 w-80 h-80 rounded-full bg-[image:radial-gradient(circle,rgba(184,149,59,0.08)_0%,transparent_70%)]"></div>

    <div class="relative max-w-5xl mx-auto px-6 sm:px-10 lg:px-8 py-16 sm:py-20 lg:py-16 text-center">
        
        {{-- Breadcrumb --}}
        <a href="{{ route('home') }}"
           class="inline-flex items-center justify-center gap-2 text-xs font-semibold uppercase tracking-[0.16em] mb-12 text-[rgba(184,149,59,0.9)] transition-opacity hover:opacity-60 group">
            <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Home
        </a>

        {{-- Main heading --}}
        <h1 class="text-4xl sm:text-5xl lg:text-5xl font-bold tracking-tight leading-[1.15] text-white">
            Get In <span class="bg-gradient-to-r from-[#d4af5b] to-[#b8953b] bg-clip-text text-transparent">Touch</span>
        </h1>
        
        {{-- Subtitle --}}
        <p class="mt-5 text-base sm:text-lg text-gray-300 max-w-2xl mx-auto leading-relaxed">
            Have questions about your stay or need assistance with your booking? We're here to help you around the clock.
        </p>
    </div>
</div>

{{-- Note: the main contact form and details are automatically appended by the global Layouts view just below this content section. --}}

@endsection
