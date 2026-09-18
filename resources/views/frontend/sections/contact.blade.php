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
            <div data-scroll-reveal class="min-w-0 space-y-5">
                @include('frontend.sections.location-map')

                @if(!empty($settings['contact_phone']) || !empty($settings['contact_email']))
                <ul class="grid gap-4 sm:grid-cols-2">
                    @if(!empty($settings['contact_phone']))
                    <li class="min-w-0">
                        <a href="tel:{{ $settings['contact_phone'] }}" class="group flex items-start gap-3 rounded-xl p-2 transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-gold">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold/20 text-[#856534]" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 3h3l1.5 5-2 1.5a14 14 0 0 0 5.5 5.5l1.5-2 5 1.5v3a3 3 0 0 1-3 3A16 16 0 0 1 3.5 6a3 3 0 0 1 3-3Z"/></svg>
                            </span>
                            <span class="min-w-0"><span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Call us</span><span class="mt-1 block break-words text-sm text-navy group-hover:text-[#856534]">{{ $settings['contact_phone'] }}</span></span>
                        </a>
                    </li>
                    @endif
                    @if(!empty($settings['contact_email']))
                    <li class="min-w-0">
                        <div class="flex items-start gap-3 rounded-xl p-2">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-gold/20 text-[#856534]" aria-hidden="true">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <span class="min-w-0"><span class="block text-[11px] font-semibold uppercase tracking-widest text-[#856534]">Email us</span>
                                @foreach(\App\Support\EmailAddresses::valid($settings['contact_email']) as $contactEmail)
                                    <a href="mailto:{{ $contactEmail }}" class="mt-1 block break-all text-sm text-navy hover:text-[#856534] focus-visible:outline-2 focus-visible:outline-gold">{{ $contactEmail }}</a>
                                @endforeach
                            </span>
                        </div>
                    </li>
                    @endif
                </ul>
                @endif

                @if(!empty($settings['contact_whatsapp']))
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 px-2 py-1 text-sm font-medium text-[#856534] underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-gold">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3 8.5 8.5 0 0 1 21 11.5Z"/></svg>
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
                                    <option value="Packages" @selected(old('category') === 'Packages')>General package enquiry</option>
                                    @foreach($packages as $package)
                                    <option value="package:{{ $package->id }}" @selected(old('category') === 'package:'.$package->id)>{{ $package->name }}</option>
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
                                  style="border-color:rgba(133,101,52,0.18); background:#fdfcf9;"
                        >{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full rounded-xl bg-[#856534] py-3.5 text-sm font-semibold text-white transition-colors hover:bg-[#71552c] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold">
                        <span>Send Message →</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</section>
