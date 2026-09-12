@extends('layouts.admin')
@section('title', 'Blogs')
@section('page_title', 'Blogs')
@section('breadcrumb', 'Admin / Blogs')
@section('content')
<div class="mb-6 flex items-center justify-between gap-4">
    <div>
        <h2 class="text-lg font-semibold text-white">Blogs ({{ $blogs->total() }})</h2>
        <p class="mt-1 text-sm text-gray-400">Share hotel news, local guides, and travel stories.</p>
    </div>
    <a href="{{ route('admin.blogs.create') }}" class="shrink-0 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">+ Add Blog</a>
</div>
<div class="overflow-x-auto rounded-2xl border border-white/8 bg-white/5">
    <table class="w-full text-sm">
        <thead><tr class="border-b border-white/8 text-left text-xs uppercase tracking-wide text-gray-400">
            <th class="px-5 py-3">Blog</th><th class="px-5 py-3">Status</th><th class="px-5 py-3">Published</th><th class="px-5 py-3 text-right">Actions</th>
        </tr></thead>
        <tbody class="divide-y divide-white/5">
            @forelse($blogs as $blog)
                <tr class="hover:bg-white/3">
                    <td class="px-5 py-4"><div class="flex items-center gap-3 min-w-48">
                        @if($blog->cover_url)<img src="{{ $blog->cover_url }}" alt="" class="h-12 w-16 shrink-0 rounded-lg object-cover">@endif
                        <span class="font-medium text-white break-words">{{ $blog->title }}</span>
                    </div></td>
                    <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs {{ $blog->is_published ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-500/15 text-gray-400' }}">{{ $blog->is_published ? 'Published' : 'Draft' }}</span></td>
                    <td class="px-5 py-4 text-gray-400 whitespace-nowrap">{{ $blog->published_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="px-5 py-4"><div class="flex items-center justify-end gap-2">
                        @if($blog->is_published)<a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" rel="noopener" class="px-3 py-1.5 text-xs text-violet-300 hover:underline">View</a>@endif
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="rounded-lg bg-white/8 px-3 py-1.5 text-xs text-gray-300 hover:bg-white/15">Edit</a>
                        <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this blog permanently?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-500/10 px-3 py-1.5 text-xs text-red-400 hover:bg-red-500/20">Delete</button>
                        </form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-12 text-center text-gray-400">No blogs yet. Add your first story to get started.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $blogs->links() }}</div>
@endsection
