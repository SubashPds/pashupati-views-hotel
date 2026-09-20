{{--
  Section: Rooms
  Props: $rooms (Collection<Room>), $settings
--}}
@if($rooms->isNotEmpty())
<section id="rooms" class="py-10 sm:py-12" style="background:#faf8f3;">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div data-scroll-reveal class="text-center mb-8 sm:mb-10">
            <span class="section-label">{{ $settings['rooms_subtitle'] ?? 'REST, BEAUTIFULLY REIMAGINED' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold mt-3" style="color:#0d1b2a;">
                {{ $settings['rooms_title'] ?? 'Your own little sanctuary.' }}
            </h2>
            @if(!empty($settings['rooms_description']))
            <p class="mt-4 text-gray-500 max-w-xl mx-auto leading-relaxed">
                {{ $settings['rooms_description'] }}
            </p>
            @endif
        </div>

        {{-- Category Filter --}}
        @php $categories = $rooms->pluck('category')->unique()->values(); @endphp
        @if($categories->count() > 1)
        <div class="flex flex-wrap items-center justify-center gap-2 mb-6 sm:mb-8" role="tablist" aria-label="Filter rooms by category">
            <button class="room-filter-btn active-filter px-5 py-2 text-xs font-semibold rounded-full transition-all"
                    data-filter="all" role="tab" aria-selected="true">All rooms</button>
            @foreach($categories as $cat)
            <button class="room-filter-btn px-5 py-2 text-xs font-semibold rounded-full transition-all"
                    data-filter="{{ $cat }}" role="tab" aria-selected="false">{{ ucfirst($cat) }}</button>
            @endforeach
        </div>
        @endif

        {{-- Room Cards Grid --}}
        <div id="rooms-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($rooms as $room)
            <article data-scroll-reveal class="room-card card-lift group relative flex flex-col rounded-2xl overflow-hidden shadow-md"
                     data-category="{{ $room->category }}"
                     style="background:#fff; border:1px solid rgba(184,149,59,0.12);">

                {{-- Image --}}
                <div class="relative h-52 overflow-hidden bg-gray-100">
                    @if($room->cover_image_url)
                        <img src="{{ $room->cover_image_url }}"
                             alt="{{ $room->name }}"
                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                             loading="lazy" decoding="async">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-6xl"
                             style="background:linear-gradient(135deg,#f5f1ea,#e8d5a3);">
                            🛏️
                        </div>
                    @endif
                    {{-- Category badge --}}
                    <div class="absolute top-3 left-3">
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-widest rounded-full text-white"
                              style="background:rgba(13,27,42,0.9);">
                            {{ ucfirst($room->category) }}
                        </span>
                    </div>
                    {{-- Price badge --}}
                    <div class="absolute bottom-3 right-3">
                        <span class="px-3 py-1.5 text-xs font-bold rounded-lg text-white"
                              style="background:rgba(13,27,42,0.9);">
                            <span data-currency-price="room-{{ $room->id }}">{{ $currency->format($room->price_per_night) }}</span><span class="font-normal opacity-75"> /night</span>
                        </span>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex flex-col flex-1 p-5">
                    <div class="mb-1">
                        @if($room->tagline)
                        <p class="section-label mb-1">{{ $room->tagline }}</p>
                        @endif
                        <h3 class="text-lg font-bold" style="color:#0d1b2a;">{{ $room->name }}</h3>
                    </div>

                    {{-- Specs --}}
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 mb-3 text-xs text-gray-500">
                        @if($room->size_sqm)
                        <span>📐 {{ $room->size_sqm }} m²</span>
                        @endif
                        @if($room->max_guests)
                        <span>👥 Up to {{ $room->max_guests }} guests</span>
                        @endif
                        @if($room->bed_type)
                        <span>🛏 {{ $room->bed_type }}</span>
                        @endif
                    </div>

                    @if($room->short_description)
                    <p class="text-sm text-gray-500 leading-relaxed mb-4 flex-1">
                        {{ Str::limit($room->short_description, 120) }}
                    </p>
                    @endif

                    {{-- Amenity chips --}}
                    @if(!empty($room->amenities) && is_array($room->amenities))
                    <div class="flex flex-wrap gap-1.5 mb-5">
                        @foreach(array_slice($room->amenities, 0, 4) as $amenity)
                        <span class="px-2 py-1 text-xs rounded-md"
                              style="background:rgba(184,149,59,0.08); color:#8a6f2e; border:1px solid rgba(184,149,59,0.20);">
                            {{ $amenity }}
                        </span>
                        @endforeach
                        @if(count($room->amenities) > 4)
                        <span class="px-2 py-1 text-xs rounded-md text-gray-400"
                              style="background:#f5f1ea;">+{{ count($room->amenities) - 4 }} more</span>
                        @endif
                    </div>
                    @endif

                    {{-- Actions --}}
                    <button type="button" data-room-details="room-details-{{ $room->id }}"
                            aria-haspopup="dialog" aria-controls="room-details-{{ $room->id }}" aria-label="View details for {{ $room->name }}"
                            class="room-details-trigger mb-3 w-full py-2 text-sm font-semibold text-navy cursor-pointer">
                        View details →
                    </button>
                    <button type="button" data-book-room="{{ $room->name }}"
                            class="relative z-20 w-full py-3 text-sm font-bold text-white rounded-xl transition-all hover:brightness-110 active:scale-95"
                            style="background:linear-gradient(135deg,#0d1b2a,#1a2d42);">
                        Book this room ↗
                    </button>
                </div>
            </article>
            @endforeach
        </div>
    </div>
</section>

@foreach($rooms as $room)
    @include('frontend.partials.room-details', ['room' => $room])
@endforeach

@push('scripts')
<script>
// Room category filter
document.querySelectorAll('.room-filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const filter = this.dataset.filter;

        // Update button styles
        document.querySelectorAll('.room-filter-btn').forEach(b => {
            b.classList.remove('active-filter');
            b.style.background = '';
            b.style.color = '';
            b.setAttribute('aria-selected', 'false');
        });
        this.classList.add('active-filter');
        this.style.background = 'linear-gradient(135deg,#b8953b,#d4af5b)';
        this.style.color = '#fff';
        this.setAttribute('aria-selected', 'true');

        // Filter cards
        document.querySelectorAll('.room-card').forEach(card => {
            const match = filter === 'all' || card.dataset.category === filter;
            card.style.display = match ? '' : 'none';
        });
    });
});

// Init default style
document.querySelector('.active-filter')?.dispatchEvent(new Event('click'));
document.querySelector('[data-filter="all"]')?.click();
</script>
@endpush

@endif
