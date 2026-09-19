@extends('layouts.admin')
@section('title', $faq->exists ? 'Edit FAQ' : 'Add FAQ')
@section('page_title', $faq->exists ? 'Edit FAQ' : 'Add FAQ')
@section('breadcrumb', 'Admin / FAQs / ' . ($faq->exists ? 'Edit' : 'Add'))

@section('content')
<div class="max-w-2xl">
<form method="POST"
      action="{{ $faq->exists ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}"
      class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
    @csrf
    @if($faq->exists) @method('PUT') @endif

    {{-- Question --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
            Question <span class="text-red-400">*</span>
        </label>
        <input type="text" name="question" required
               value="{{ old('question', $faq->question) }}"
               placeholder="e.g. What time is check-in?"
               class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-violet-500 transition-all">
        @error('question')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Answer --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
            Answer <span class="text-red-400">*</span>
        </label>
        <textarea name="answer" required rows="5"
                  placeholder="Provide a clear, helpful answer..."
                  class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:border-violet-500 transition-all resize-y">{{ old('answer', $faq->answer) }}</textarea>
        @error('answer')
            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Sort Order --}}
    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Sort Order</label>
        <input type="number" name="sort_order" min="0"
               value="{{ old('sort_order', $faq->sort_order ?? 0) }}"
               class="w-32 px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 transition-all">
        <p class="text-xs text-gray-600 mt-1">Lower numbers appear first.</p>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            {{ $faq->exists ? 'Update FAQ' : 'Create FAQ' }}
        </button>
        <a href="{{ route('admin.faqs.index') }}"
           class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm rounded-xl transition-colors">
            Cancel
        </a>
    </div>
</form>
</div>
@endsection
