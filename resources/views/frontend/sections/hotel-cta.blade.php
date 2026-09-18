@php
    $stay = collect(\App\Support\StaySettings::defaults())->merge($settings);
@endphp
@if($stay['stay_enabled'])
<section class="bg-[#f9f4eb] py-10 sm:py-12">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div data-scroll-reveal class="rounded-[32px] border border-[#d9c58f]/50 bg-white p-6 shadow-[0_20px_60px_rgba(13,27,42,0.06)] sm:p-8 lg:p-10">
            <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <span class="inline-flex rounded-full border border-[#856534]/20 bg-[#f4ebd7] px-3 py-1 text-[10px] font-semibold uppercase tracking-[0.22em] text-[#856534]">
                        {{ $stay['stay_eyebrow'] }}
                    </span>
                    <h2 class="mt-4 text-3xl font-bold text-[#0d1b2a] sm:text-4xl">
                        {{ $stay['stay_title'] }}
                    </h2>
                    <p class="mt-3 text-base text-gray-600 leading-relaxed">
                        {{ $stay['stay_description'] }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="#rooms" class="inline-flex items-center justify-center rounded-xl bg-[#0d1b2a] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#1a2d42]">
                        {{ $stay['stay_rooms_label'] }}
                    </a>
                    @if(isset($packages) && $packages->isNotEmpty())
                    <a href="#packages" class="inline-flex items-center justify-center rounded-xl border border-[#856534]/30 bg-[#f4ebd7] px-5 py-3 text-sm font-semibold text-[#856534] transition hover:bg-[#eadbb4]">
                        {{ $stay['stay_packages_label'] }}
                    </a>
                    @endif
                    <a href="#contact" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                        {{ $stay['stay_contact_label'] }}
                    </a>
                </div>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-3">
                @foreach([1, 2, 3] as $card)
                <div class="rounded-2xl border border-gray-200 bg-[#fffdf8] p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#856534]">{{ $stay['stay_card_'.$card.'_label'] }}</p>
                    <h3 class="mt-3 text-xl font-bold text-[#0d1b2a]">{{ $stay['stay_card_'.$card.'_title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $stay['stay_card_'.$card.'_description'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
