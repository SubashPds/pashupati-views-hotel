@extends('layouts.admin')
@section('title','FAQs')
@section('page_title','FAQs')
@section('breadcrumb','Admin / FAQs')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-lg font-semibold text-white">Frequently Asked Questions ({{ $faqs->count() }})</h2>
    <a href="{{ route('admin.faqs.create') }}"
       class="px-4 py-2 text-sm font-semibold bg-violet-600 hover:bg-violet-500 text-white rounded-xl transition-colors">
        + Add FAQ
    </a>
</div>

<div class="space-y-3">
    @forelse($faqs as $faq)
    <div class="p-5 rounded-2xl bg-white/5 border border-white/8">
        <div class="flex items-start justify-between gap-4 mb-2">
            <p class="font-semibold text-white text-sm leading-snug flex-1">{{ $faq->question }}</p>
            <div class="flex items-center gap-2 shrink-0">
                <form method="POST" action="{{ route('admin.faqs.toggle-status', $faq) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="px-2.5 py-1 text-xs font-medium rounded-full ring-1 {{ $faq->is_active ? 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20' : 'bg-gray-500/15 text-gray-400 ring-gray-400/20' }}">
                        {{ $faq->is_active ? 'Active' : 'Inactive' }}
                    </button>
                </form>
                <a href="{{ route('admin.faqs.edit', $faq) }}"
                   class="px-3 py-1 text-xs font-medium bg-white/8 hover:bg-white/15 text-gray-300 rounded-lg transition-colors">
                    Edit
                </a>
                <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}"
                      onsubmit="return confirm('Delete this FAQ?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-3 py-1 text-xs font-medium bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded-lg transition-colors">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        <p class="text-sm text-gray-400 leading-relaxed">{{ $faq->answer }}</p>
        <p class="text-xs text-gray-600 mt-3">Sort: {{ $faq->sort_order }}</p>
    </div>
    @empty
    <div class="text-center py-20 text-gray-500">
        <div class="text-5xl mb-3">❓</div>
        <p class="font-medium text-gray-400">No FAQs yet.</p>
        <p class="text-sm mt-1">Add your first FAQ to help guests find answers quickly.</p>
        <a href="{{ route('admin.faqs.create') }}"
           class="inline-block mt-4 px-5 py-2.5 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            + Add FAQ
        </a>
    </div>
    @endforelse
</div>
@endsection
