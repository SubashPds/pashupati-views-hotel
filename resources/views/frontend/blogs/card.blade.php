<a href="{{ route('blogs.show', $blog->slug) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gold/15 bg-white shadow-sm transition-all hover:shadow-lg focus-visible:outline-2 focus-visible:outline-gold">
    <div class="aspect-[16/10] overflow-hidden bg-[#eee6d6]">
        @if($blog->cover_url)
            <img src="{{ $blog->cover_url }}" alt="{{ $blog->title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center text-[#856534]" aria-hidden="true">
                <svg class="h-14 w-14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M4 4h6a3 3 0 0 1 3 3v14a4 4 0 0 0-4-3H4V4Zm16 0h-4a3 3 0 0 0-3 3v14a4 4 0 0 1 4-3h3V4Z"/></svg>
            </div>
        @endif
    </div>
    <div class="flex flex-1 flex-col p-6">
        <time datetime="{{ $blog->published_at->toDateString() }}" class="text-xs font-medium text-[#856534]">{{ $blog->published_at->format('M j, Y') }}</time>
        <h3 class="mt-3 text-xl font-semibold leading-snug text-navy break-words group-hover:text-[#856534]">{{ $blog->title }}</h3>
        <p class="mt-3 text-sm leading-relaxed text-gray-600 break-words">{{ \Illuminate\Support\Str::limit($blog->summary, 180) }}</p>
        <span class="mt-auto pt-6 text-sm font-semibold text-[#856534]">Read story <span aria-hidden="true">↗</span></span>
    </div>
</a>
