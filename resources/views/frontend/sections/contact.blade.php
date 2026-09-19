{{--
  Section: Contact / Get in Touch
  Props: $settings
--}}
<section id="contact" class="bg-ivory py-10 sm:py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-scroll-reveal class="mb-8 sm:mb-10 grid gap-5 lg:grid-cols-2 lg:items-end lg:gap-12">
            <div>
                <span class="section-label" style="color:#856534;">GET IN TOUCH</span>
                <h2 class="mt-3 text-3xl font-semibold leading-tight tracking-tight text-navy sm:text-4xl">A peaceful stay near the sacred heart of Kathmandu.</h2>
            </div>
            <p class="max-w-lg text-sm leading-relaxed text-gray-600">Whether you are planning a spiritual retreat, a family getaway, or a premium city stay, our team is here to help you choose the perfect experience.</p>
        </div>

        <div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-2 lg:gap-8">
            <div data-scroll-reveal class="min-w-0 space-y-6">

                @if(!empty($settings['contact_address']))
                <div class="flex items-start gap-3 rounded-xl p-3 bg-white/50 border border-gold/10">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gold/20 text-[#856534]" aria-hidden="true">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                    </span>
                    <div>
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Address</span>
                        <span class="mt-1 block text-sm text-navy leading-relaxed">{{ $settings['contact_address'] }}</span>
                    </div>
                </div>
                @endif
                @if(!empty($settings['contact_address']))
                <div class="flex items-start gap-3 rounded-xl p-3 bg-white/50 border border-gold/10">
                    <a href="tel:{{ $settings['contact_phone'] }}" class="group flex items-start gap-3 rounded-xl p-2 transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-gold">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold/20 text-[#856534]" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.5 3h3l1.5 5-2 1.5a14 14 0 0 0 5.5 5.5l1.5-2 5 1.5v3a3 3 0 0 1-3 3A16 16 0 0 1 3.5 6a3 3 0 0 1 3-3Z" />
                            </svg>
                        </span>
                        <span class="min-w-0"><span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Call us</span><span class="mt-1 block break-words text-sm text-navy group-hover:text-[#856534]">{{ $settings['contact_phone'] }}</span></span>
                    </a>
                </div>
                @endif
                @if(!empty($settings['contact_email']))
                <div class="flex items-start gap-3 rounded-xl p-3 bg-white/50 border border-gold/10">
                    <div class="flex items-start gap-3 rounded-xl p-2">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold/20 text-[#856534]" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="5" width="18" height="14" rx="2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6" />
                            </svg>
                        </span>
                        <span class="min-w-0"><span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Email us</span>
                            @foreach(\App\Support\EmailAddresses::valid($settings['contact_email']) as $contactEmail)
                            <a href="mailto:{{ $contactEmail }}" class="mt-1 block break-all text-sm text-navy hover:text-[#856534] focus-visible:outline-2 focus-visible:outline-gold">{{ $contactEmail }}</a>
                            @endforeach
                        </span>
                    </div>
                </div>
                @endif

                @if(!empty($settings['social_facebook']) || !empty($settings['social_instagram']) || !empty($settings['social_tiktok']))
                <div class="flex items-start gap-3 rounded-xl p-3 bg-white/50 border border-gold/10">
                    <div class="flex items-start gap-4 p-2 w-full flex-col">
                        <span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Follow Us On</span>
                        <div class="flex items-center gap-5">
                            @if(!empty($settings['social_facebook']))
                            <a href="{{ $settings['social_facebook'] }}" target="_blank" rel="noopener noreferrer" class="text-[#856534] hover:text-[#71552c] transition-colors focus-visible:outline-2 focus-visible:outline-gold">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            @endif
                            @if(!empty($settings['social_instagram']))
                            <a href="{{ $settings['social_instagram'] }}" target="_blank" rel="noopener noreferrer" class="text-[#856534] hover:text-[#71552c] transition-colors focus-visible:outline-2 focus-visible:outline-gold">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            @endif
                            @if(!empty($settings['social_tiktok']))
                            <a href="{{ $settings['social_tiktok'] }}" target="_blank" rel="noopener noreferrer" class="text-[#856534] hover:text-[#71552c] transition-colors focus-visible:outline-2 focus-visible:outline-gold">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 30 30" aria-hidden="true">
                                    <path d="M16.656 1.029c1.637-0.025 3.262-0.012 4.886-0.025 0.054 2.031 0.878 3.859 2.189 5.213l-0.002-0.002c1.411 1.271 3.247 2.095 5.271 2.235l0.028 0.002v5.036c-1.912-0.048-3.71-0.489-5.331-1.247l0.082 0.034c-0.784-0.377-1.447-0.764-2.077-1.196l0.052 0.034c-0.012 3.649 0.012 7.298-0.025 10.934-0.103 1.853-0.719 3.543-1.707 4.954l0.020-0.031c-1.652 2.366-4.328 3.919-7.371 4.011l-0.014 0c-0.123 0.006-0.268 0.009-0.414 0.009-1.73 0-3.347-0.482-4.725-1.319l0.040 0.023c-2.508-1.509-4.238-4.091-4.558-7.094l-0.004-0.041c-0.025-0.625-0.037-1.25-0.012-1.862 0.49-4.779 4.494-8.476 9.361-8.476 0.547 0 1.083 0.047 1.604 0.136l-0.056-0.008c0.025 1.849-0.050 3.699-0.050 5.548-0.423-0.153-0.911-0.242-1.42-0.242-1.868 0-3.457 1.194-4.045 2.861l-0.009 0.030c-0.133 0.427-0.21 0.918-0.21 1.426 0 0.206 0.013 0.41 0.037 0.61l-0.002-0.024c0.332 2.046 2.086 3.59 4.201 3.59 0.061 0 0.121-0.001 0.181-0.004l-0.009 0c1.463-0.044 2.733-0.831 3.451-1.994l0.010-0.018c0.267-0.372 0.45-0.822 0.511-1.311l0.001-0.014c0.125-2.237 0.075-4.461 0.087-6.698 0.012-5.036-0.012-10.060 0.025-15.083z"></path>
                                </svg>
                                @endif
                        </div>
                    </div>
                </div>
                @endif

                @if(!empty($settings['contact_whatsapp']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-2 py-1 text-sm font-medium text-[#856534] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-gold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3 8.5 8.5 0 0 1 21 11.5Z" />
                    </svg>
                    Chat with us on WhatsApp <span aria-hidden="true">↗</span>
                </a>
                @endif
            </div>

            {{-- Right: Enquiry form --}}
            <div data-scroll-reveal class="min-w-0 overflow-hidden rounded-2xl border border-gold/15 bg-white shadow-sm">

                @if(session('enquiry_success'))
                <div class="p-8 text-center">
                    <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-ivory text-2xl text-[#856534]" aria-hidden="true">✓</div>
                    <h3 class="text-xl font-bold mb-2" style="color:#0d1b2a;">Message Received!</h3>
                    <p class="text-gray-500">Thank you for reaching out. We'll get back to you within 24 hours.</p>
                </div>
                @else

                <div class="px-6 pt-6 sm:px-7 sm:pt-7">
                    <h3 class="text-xl font-semibold text-navy">Send us a message</h3>
                    <p class="text-xs text-gray-500 mt-0.5">We respond within 24 hours.</p>
                </div>

                <form method="POST" action="{{ route('enquire') }}" class="space-y-4 px-6 pb-6 pt-5 sm:px-7 sm:pb-7" novalidate>
                    @csrf

                    @if($errors->has('contact'))
                    <div class="p-3 rounded-lg text-xs text-red-600 bg-red-50 border border-red-200">
                        {{ $errors->first('contact') }}
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="c-name" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#856534;">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input id="c-name" type="text" name="guest_name" required
                                value="{{ old('guest_name') }}"
                                placeholder="Your full name"
                                class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-gold/20 transition-all {{ $errors->has('guest_name') ? 'border-red-300' : '' }}"
                                style="border-color:rgba(133,101,52,0.18); background:#fdfcf9;"
                                autocomplete="name">
                            @error('guest_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="c-cat" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#856534;">Enquiry Type</label>
                            <select id="c-cat" name="category"
                                class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-gold/20 transition-all bg-[#fdfcf9]"
                                style="border-color:rgba(133,101,52,0.18);">
                                @foreach(['Room & stay','Packages','Dining','Events / Private functions','Airport transfer','General enquiry'] as $cat)
                                @if($cat === 'Packages' && isset($packages) && $packages->isNotEmpty())
                                <optgroup label="Packages">
                                    <option value="Packages" @selected(old('category')==='Packages' )>General package enquiry</option>
                                    @foreach($packages as $package)
                                    <option value="package:{{ $package->id }}" @selected(old('category')==='package:' .$package->id)>{{ $package->name }}</option>
                                    @endforeach
                                </optgroup>
                                @else
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endif
                                @endforeach
                            </select>
                            @error('category') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="c-phone" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#856534;">Phone</label>
                            <input id="c-phone" type="tel" name="phone"
                                value="{{ old('phone') }}"
                                placeholder="+977…"
                                class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-gold/20 transition-all"
                                style="border-color:rgba(133,101,52,0.18); background:#fdfcf9;"
                                autocomplete="tel">
                        </div>
                        <div>
                            <label for="c-email" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#856534;">Email</label>
                            <input id="c-email" type="email" name="email"
                                value="{{ old('email') }}"
                                placeholder="your@email.com"
                                class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-gold/20 transition-all"
                                style="border-color:rgba(133,101,52,0.18); background:#fdfcf9;"
                                autocomplete="email">
                        </div>
                    </div>

                    <div>
                        <label for="c-msg" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#856534;">Message</label>
                        <textarea id="c-msg" name="message" rows="4"
                            placeholder="Tell us how we can help…"
                            class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none focus:ring-2 focus:ring-gold/20 transition-all resize-y"
                            style="border-color:rgba(133,101,52,0.18); background:#fdfcf9;">{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                        class="w-full rounded-xl bg-[#856534] py-3.5 text-sm font-semibold text-white transition-colors hover:bg-[#71552c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold">
                        <span>Send Message →</span>
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Map Section Below --}}
        <div data-scroll-reveal class="mt-10 lg:mt-12">
            @include('frontend.sections.location-map')
        </div>
    </div>
</section>