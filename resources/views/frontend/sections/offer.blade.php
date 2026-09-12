@if($promotions->isNotEmpty() && !$errors->any() && !session('enquiry_success'))
<dialog id="promotion-dialog" class="promotion-dialog" aria-labelledby="promotion-dialog-label">
    <h2 id="promotion-dialog-label" class="sr-only">Offers &amp; Highlights</h2>
    <div class="promotion-stack">
        @foreach($promotions as $promotion)
            <article data-promotion-card class="promotion-panel" data-stack-depth="{{ min($loop->index, 2) }}"
                     style="--stack-depth: {{ min($loop->index, 2) }}; --stack-order: {{ $promotions->count() - $loop->index }};"
                     @if(!$loop->first) inert aria-hidden="true" @endif
                     @if($loop->index > 2) hidden @endif
                     aria-labelledby="promotion-title-{{ $promotion->id }}">
                <header class="flex items-center justify-between gap-3 px-5 py-3 border-b border-gold/15 shrink-0">
                    <p class="text-xs font-semibold tracking-widest uppercase text-[#856534]">Offers &amp; Highlights</p>
                    <div class="flex items-center gap-2">
                        @if($promotions->count() > 1)
                            <button type="button" data-close-promotions class="text-xs text-gray-600 underline px-2 py-2">Close all</button>
                        @endif
                        <button type="button" data-dismiss-promotion aria-label="Dismiss promotion: {{ $promotion->title }}" @if($loop->first) autofocus @endif class="promotion-icon-button text-2xl">×</button>
                    </div>
                </header>
                <div class="promotion-body overflow-y-auto min-h-0" data-promotion-body>
                    @if($promotion->image_url)
                        <img src="{{ $promotion->image_url }}" alt="{{ $promotion->image_alt ?? '' }}" class="promotion-image" @if(!$loop->first) loading="lazy" @endif>
                    @endif
                    <div class="offer-card-content p-6 sm:p-8">
                        @if($promotion->label)<p class="text-xs font-semibold uppercase tracking-widest text-[#856534] mb-3">{{ $promotion->label }}</p>@endif
                        <h2 id="promotion-title-{{ $promotion->id }}" class="text-2xl sm:text-3xl font-semibold leading-tight text-navy">{{ $promotion->title }}</h2>
                        @if($promotion->description)<p class="mt-3 text-sm leading-relaxed text-gray-600 whitespace-pre-line">{{ $promotion->description }}</p>@endif
                        @if($promotion->button_text)
                            <div class="mt-5">
                                @if($promotion->button_url)
                                    <a data-promotion-link href="{{ str_starts_with($promotion->button_url, '#') ? route('home').$promotion->button_url : $promotion->button_url }}" class="offer-card-button">{{ $promotion->button_text }} <span aria-hidden="true">↗</span></a>
                                @else
                                    <button type="button" data-promotion-book class="offer-card-button">{{ $promotion->button_text }} <span aria-hidden="true">↗</span></button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <p data-promotion-status role="status" class="sr-only"></p>
</dialog>
@endif
