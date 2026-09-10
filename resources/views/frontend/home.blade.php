@extends('layouts.app')

@section('title', ($settings['site_name'] ?? 'Pashupati Views Hotel') . ' — ' . ($settings['site_tagline'] ?? 'Where Comfort Meets Devotion'))
@section('meta_description', 'A premium hotel experience beside the sacred Pashupatinath Temple, Kathmandu, Nepal. Book your stay at Pashupati Views Hotel.')

@section('content')

    {{-- ① Hero ─────────────────────────────────────────────────────────── --}}
    @include('frontend.sections.hero')

    {{-- ② Rooms ──────────────────────────────────────────────────────────── --}}
    @include('frontend.sections.rooms')

    {{-- ③ Packages ──────────────────────────────────────────────────────── --}}
    @include('frontend.sections.packages')

    {{-- ④ Experience / Amenity highlights ────────────────────────────────── --}}
    @include('frontend.sections.experience')

    {{-- ⑤ Services ───────────────────────────────────────────────────────── --}}
    @include('frontend.sections.services')


    {{-- ⑤ Gallery ───────────────────────────────────────────────────────── --}}
    @include('frontend.sections.gallery')

    {{-- ⑥ Testimonials ────────────────────────────────────────────────── --}}
    @include('frontend.sections.testimonials')

    {{-- ⑦ Contact ──────────────────────────────────────────────────────── --}}
    @include('frontend.sections.contact')

@endsection
