{{--
  Section: Services
  Props: $services (Collection<Service>), $settings
--}}
@if($services->isNotEmpty())
<section id="services" class="py-10 sm:py-12" style="background:#fff;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div data-scroll-reveal class="text-center mb-8 sm:mb-10">
            <span class="section-label">{{ $settings['services_subtitle'] ?? 'SERVICES & CONVENIENCES' }}</span>
            <div class="divider-gold mx-auto my-3"></div>
            <h2 class="text-3xl sm:text-4xl font-bold mt-3" style="color:#0d1b2a;">
                {{ $settings['services_title'] ?? 'Thoughtfully arranged.' }}
            </h2>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
            <div data-scroll-reveal class="relative group p-6 rounded-2xl border transition-all duration-300 card-lift"
                 style="background:#faf8f3; border-color:rgba(184,149,59,0.15);">
                <div class="text-4xl mb-4 transition-transform duration-300 group-hover:scale-110">
                    {{ $service->icon ?? '✦' }}
                </div>
                <h3 class="font-bold text-base mb-2" style="color:#0d1b2a;">{{ $service->title }}</h3>
                @if($service->description)
                <p class="text-sm text-gray-500 leading-relaxed mb-4">{{ $service->description }}</p>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold" style="color:#b8953b;">{{ $currency->label($service->price_label) }}</span>
                    <button onclick="openBooking()"
                            class="text-xs font-bold px-3 py-1.5 rounded-lg border transition-all hover:bg-amber-50 active:scale-95"
                            style="color:#b8953b; border-color:rgba(184,149,59,0.30);">
                        Add to stay ↗
                    </button>
                </div>
                {{-- Decorative corner accent --}}
                <div class="absolute top-0 right-0 w-16 h-16 rounded-tr-2xl overflow-hidden pointer-events-none">
                    <div class="absolute top-0 right-0 w-8 h-8 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                         style="background:linear-gradient(135deg,transparent 50%,rgba(184,149,59,0.1) 50%);"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif
