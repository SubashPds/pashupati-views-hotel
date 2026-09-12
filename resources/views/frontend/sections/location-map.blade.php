@php
    $hotelName = trim($settings['site_name'] ?? '') ?: 'Pashupati Views Hotel';
    $hotelAddress = trim($settings['contact_address'] ?? '');
    $mapLocation = trim($settings['contact_map_location'] ?? '')
        ?: trim($hotelName . ', ' . ($hotelAddress ?: 'Kathmandu, Nepal'));
    $mapUrl = 'https://www.google.com/maps?' . http_build_query([
        'q' => $mapLocation,
        'output' => 'embed',
    ], '', '&', PHP_QUERY_RFC3986);
    $directionsUrl = 'https://www.google.com/maps/dir/?' . http_build_query([
        'api' => 1,
        'destination' => $mapLocation,
    ], '', '&', PHP_QUERY_RFC3986);
@endphp

<div class="overflow-hidden rounded-2xl border border-gold/15 bg-white shadow-sm" aria-labelledby="location-heading">
    <iframe src="{{ $mapUrl }}" title="Map showing {{ $hotelName }} location"
            class="block h-64 w-full border-0 bg-ivory sm:h-80"
            loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    <div class="flex flex-wrap items-center justify-between gap-4 border-t border-gold/10 px-5 py-5 sm:px-6">
        <div class="min-w-0 flex-1">
            <h3 id="location-heading" class="text-sm font-semibold text-navy">{{ $hotelName }}</h3>
            <p class="mt-1 text-xs leading-relaxed text-gray-600">{{ $hotelAddress ?: 'Kathmandu, Nepal' }}</p>
        </div>
        <a href="{{ $directionsUrl }}" target="_blank" rel="noopener noreferrer"
           class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-ivory px-3.5 py-2.5 text-xs font-semibold text-[#856534] transition-colors hover:bg-[#eee6d6] focus-visible:outline-2 focus-visible:outline-gold">
            Get directions
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17 17 7M7 7h10v10"/></svg>
        </a>
    </div>
</div>
