@extends('layouts.admin')
@section('title','Policies')
@section('page_title','Policies')
@section('breadcrumb','Admin / Policies')

@push('head')
<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
<link  href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" rel="stylesheet">
@endpush

@section('content')

<div class="mb-6">
    <h2 class="text-lg font-semibold text-white">Policies</h2>
    <p class="text-sm text-gray-400 mt-1">Manage your website's Privacy Policy and Terms & Conditions. These two fixed policy categories are displayed in the website footer when active.</p>
</div>

<div class="space-y-6">
    @forelse($policies as $category => $policy)
    <div class="p-6 rounded-2xl bg-white/5 border border-white/8">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-white">{{ $policy->title }}</h3>
                <p class="text-xs text-gray-500 mt-1">Category: <span class="text-gray-400">{{ $policy->category }}</span></p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('admin.policies.update', $policy) }}" class="inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="is_active" value="{{ $policy->is_active ? '0' : '1' }}">
                    <button type="submit"
                            class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 {{ $policy->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20' }}">
                        {{ $policy->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
                <a href="{{ route('admin.policies.edit', $policy) }}"
                   class="px-3 py-1 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">
                    Edit
                </a>
            </div>
        </div>
        <div class="text-sm text-gray-400 leading-relaxed line-clamp-3">
            {{ strip_tags($policy->description) }}
        </div>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <div class="text-5xl mb-3">📜</div>
        <p class="font-medium text-gray-400">No policies found.</p>
    </div>
    @endforelse
</div>

@endsection
