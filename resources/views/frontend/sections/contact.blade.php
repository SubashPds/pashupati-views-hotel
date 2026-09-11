{{--
  Section: Contact / Get in Touch
  Props: $settings
--}}
<section id="contact" class="py-24" style="background:#faf8f3;">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-start">

            {{-- Left: info --}}
            <div>
                <span class="section-label">GET IN TOUCH</span>
                <div class="divider-gold my-3"></div>
                <h2 class="text-3xl sm:text-4xl font-bold mt-3 mb-5" style="color:#0d1b2a;">We'd love to hear from you.</h2>
                <p class="text-gray-500 leading-relaxed mb-8">
                    Whether you have a question about our rooms, would like to plan your stay, or simply want to learn more — our team is here to help.
                </p>

                <ul class="space-y-4 mb-8">
                    @if(!empty($settings['contact_address']))
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                             style="background:rgba(184,149,59,0.10); color:#b8953b;">📍</div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Address</p>
                            <p class="text-sm text-gray-700">{{ $settings['contact_address'] }}</p>
                        </div>
                    </li>
                    @endif
                    @if(!empty($settings['contact_phone']))
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                             style="background:rgba(184,149,59,0.10); color:#b8953b;">📞</div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Phone</p>
                            <a href="tel:{{ $settings['contact_phone'] }}" class="text-sm text-gray-700 hover:text-amber-600 transition-colors">{{ $settings['contact_phone'] }}</a>
                        </div>
                    </li>
                    @endif
                    @if(!empty($settings['contact_email']))
                    <li class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg"
                             style="background:rgba(184,149,59,0.10); color:#b8953b;">✉️</div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Email</p>
                            <a href="mailto:{{ $settings['contact_email'] }}" class="text-sm text-gray-700 hover:text-amber-600 transition-colors">{{ $settings['contact_email'] }}</a>
                        </div>
                    </li>
                    @endif
                </ul>

                {{-- Direct action buttons --}}
                <div class="flex flex-wrap gap-3">
                    @if(!empty($settings['contact_whatsapp']))
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings['contact_whatsapp']) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold text-white rounded-xl transition-all hover:brightness-110 shadow-sm"
                       style="background:#25d366;">
                        💬 WhatsApp ↗
                    </a>
                    @endif
                    @if(!empty($settings['contact_phone']))
                    <a href="tel:{{ $settings['contact_phone'] }}"
                       class="inline-flex items-center gap-2 px-5 py-3 text-sm font-semibold rounded-xl border transition-all hover:bg-gray-50"
                       style="color:#0d1b2a; border-color:rgba(13,27,42,0.2);">
                        📞 Call Us ↗
                    </a>
                    @endif
                </div>
            </div>

            {{-- Right: Enquiry form --}}
            <div class="rounded-2xl overflow-hidden shadow-xl"
                 style="background:#fff; border:1px solid rgba(184,149,59,0.12);">

                @if(session('enquiry_success'))
                <div class="p-8 text-center">
                    <div class="text-5xl mb-4">✅</div>
                    <h3 class="text-xl font-bold mb-2" style="color:#0d1b2a;">Message Received!</h3>
                    <p class="text-gray-500">Thank you for reaching out. We'll get back to you within 24 hours.</p>
                </div>
                @else

                <div class="px-6 py-5 border-b" style="border-color:rgba(184,149,59,0.10); background:#faf8f3;">
                    <h3 class="font-bold text-base" style="color:#0d1b2a;">Send us a message</h3>
                    <p class="text-xs text-gray-500 mt-0.5">We respond within 24 hours.</p>
                </div>

                <form method="POST" action="{{ route('enquire') }}" class="px-6 py-6 space-y-4" novalidate>
                    @csrf

                    @if($errors->has('contact'))
                    <div class="p-3 rounded-lg text-xs text-red-600 bg-red-50 border border-red-200">
                        {{ $errors->first('contact') }}
                    </div>
                    @endif

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="c-name" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input id="c-name" type="text" name="guest_name" required
                                   value="{{ old('guest_name') }}"
                                   placeholder="Your full name"
                                   class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none transition-all {{ $errors->has('guest_name') ? 'border-red-300' : '' }}"
                                   style="border-color:rgba(184,149,59,0.25); background:#faf8f3;"
                                   autocomplete="name">
                            @error('guest_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="c-cat" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Enquiry Type</label>
                            <select id="c-cat" name="category"
                                    class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none transition-all bg-[#faf8f3]"
                                    style="border-color:rgba(184,149,59,0.25);">
                                @foreach(['Room & stay','Packages','Dining','Events / Private functions','Airport transfer','General enquiry'] as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="c-phone" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Phone</label>
                            <input id="c-phone" type="tel" name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="+977…"
                                   class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none transition-all"
                                   style="border-color:rgba(184,149,59,0.25); background:#faf8f3;"
                                   autocomplete="tel">
                        </div>
                        <div>
                            <label for="c-email" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Email</label>
                            <input id="c-email" type="email" name="email"
                                   value="{{ old('email') }}"
                                   placeholder="your@email.com"
                                   class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none transition-all"
                                   style="border-color:rgba(184,149,59,0.25); background:#faf8f3;"
                                   autocomplete="email">
                        </div>
                    </div>

                    <div>
                        <label for="c-msg" class="block text-xs font-semibold uppercase tracking-wider mb-1.5" style="color:#b8953b;">Message</label>
                        <textarea id="c-msg" name="message" rows="4"
                                  placeholder="Tell us how we can help…"
                                  class="w-full px-4 py-3 text-sm rounded-xl border focus:outline-none transition-all resize-none"
                                  style="border-color:rgba(184,149,59,0.25); background:#faf8f3;"
                        >{{ old('message') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3.5 text-sm font-bold text-white rounded-xl transition-all hover:brightness-110 active:scale-[0.99] shadow-md"
                            style="background:linear-gradient(135deg,#b8953b,#d4af5b);">
                        <span data-lang="en">Send Message →</span>
                        <span data-lang="ne" class="deva" style="display:none;">सन्देश पठाउनुहोस् →</span>
                        <span data-lang="hi" class="deva" style="display:none;">संदेश भेजें →</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</section>
