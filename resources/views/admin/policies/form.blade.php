@extends('layouts.admin')
@section('title', 'Edit ' . $policy->title)
@section('page_title', 'Edit ' . $policy->title)
@section('breadcrumb', 'Admin / Policies / ' . $policy->title)

@push('head')
<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
<link  href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" rel="stylesheet">
@endpush

@section('content')

<div class="max-w-4xl">
    <form method="POST" action="{{ route('admin.policies.update', $policy) }}" class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-6">
        @csrf @method('PUT')

        {{-- Title --}}
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Title <span class="text-red-400">*</span>
            </label>
            <input type="text" name="title" required
                   value="{{ old('title', $policy->title) }}"
                   class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-violet-500 transition-all">
            @error('title')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category (read-only) --}}
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Category
            </label>
            <div class="px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-gray-300">
                {{ App\Models\Policy::CATEGORIES[$policy->category] ?? ucfirst(str_replace('_', ' ', $policy->category)) }}
            </div>
            <p class="text-xs text-gray-600 mt-1">Category cannot be changed. This is a fixed policy type.</p>
        </div>

        {{-- Description (CKEditor) --}}
        <div>
            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                Description <span class="text-red-400">*</span>
            </label>
            <textarea id="description" name="description" required class="ck-policy-editor">{{ old('description', $policy->description) }}</textarea>
            @error('description')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" class="w-4 h-4 accent-violet-500"
                       {{ old('is_active', $policy->is_active) ? 'checked' : '' }}>
                <span class="text-sm text-gray-300">Active (visible on website footer)</span>
            </label>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit"
                    class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
                Update Policy
            </button>
            <a href="{{ route('admin.policies.index') }}"
               class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm rounded-xl transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}" type="module">
    const { ClassicEditor, Autoformat, Bold, Italic, Underline, Essentials, Heading, Link, List, Paragraph, Table } = CKEDITOR;
    ClassicEditor.create(document.querySelector('#description'), {
        plugins: [Essentials, Bold, Italic, Underline, Heading, List, Link, Paragraph, Autoformat, Table],
        toolbar: ['heading','|','bold','italic','underline','|','bulletedList','numberedList','|','link','insertTable','|','undo','redo'],
    });
</script>
@endpush

@endsection
