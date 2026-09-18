@extends('layouts.admin')
@section('title','Site Settings')
@section('page_title','Site Settings')
@section('breadcrumb','Admin / Settings')

@push('head')
<script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
<link  href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" rel="stylesheet">
@endpush

@section('content')

<p class="mb-5 text-sm text-gray-400">Manage popup offers in <a href="{{ route('admin.promotions.index') }}" class="text-violet-300 underline">Offers &amp; Advertising</a>.</p>

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('POST')

    {{-- Tab navigation --}}
    @php
        $groups = $settings->keys()->toArray();
        $activeGroup = old('_settings_tab', session('settings_tab', $groups[0] ?? 'general'));
        foreach ($settings as $groupKey => $groupItems) {
            if ($groupItems->contains(fn ($setting) => $errors->has($setting->key))) {
                $activeGroup = $groupKey;
                break;
            }
        }
        if (!in_array($activeGroup, $groups, true)) $activeGroup = $groups[0] ?? 'general';
        $groupLabels = [
            'general'      => '🌐 General',
            'currency'     => '💱 Currency',
            'hero'         => '🦸 Hero',
            'stay'         => '🧳 Plan your stay',
            'rooms'        => '🛏️ Rooms',
            'packages'     => '🎁 Packages',
            'experience'   => '✨ Experience',
            'gallery'      => '🖼️ Gallery',
            'services'     => '🛎️ Services',
            'testimonials' => '💬 Testimonials',
            'contact'      => '📞 Contact',
            'footer'       => '🔻 Footer',
        ];
    @endphp

    <input type="hidden" name="_settings_tab" id="settings-active-tab" value="{{ $activeGroup }}">
    <div class="mb-6 flex flex-wrap gap-2" id="settings-tabs">
        @foreach($settings->keys() as $i => $group)
        <button type="button" data-tab="{{ $group }}"
                class="settings-tab-btn px-3.5 py-1.5 text-xs font-medium rounded-lg border transition-colors
                       {{ $group === $activeGroup ? 'bg-violet-600 border-violet-600 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white' }}">
            {{ $groupLabels[$group] ?? ucfirst($group) }}
        </button>
        @endforeach
    </div>

    @foreach($settings as $group => $items)
    <div id="tab-{{ $group }}" class="settings-panel {{ $group === $activeGroup ? '' : 'hidden' }}">
        <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
            <h3 class="text-sm font-semibold text-gray-200 mb-2">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>
            @if($group === 'stay')
                <p class="text-sm text-gray-400">Edit the section text, button labels, and three cards. The packages button appears automatically when at least one active package is available.</p>
            @endif
            @if($group === 'currency')
                <p class="text-sm text-gray-400">Keep room and package base prices in NPR. Visitors in Nepal see NPR, visitors in India see INR, and visitors elsewhere see USD. Converted price = NPR price ÷ the rate below.</p>
                <p class="text-xs text-gray-400">Choose the rates your hotel wants to use. Initial reference values are 1 INR = NPR 1.60 and 1 USD = NPR 153.01 (NRB USD selling rate published 11 September 2026). Rates change only when you update these settings.</p>
            @endif
            @foreach($items->sortBy('sort_order') as $setting)
            <div>
                <label id="label_{{ $setting->key }}" for="setting_{{ $setting->key }}" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                    {{ $setting->label }}
                </label>

                @if($setting->group === 'currency' && $setting->type === 'number')
                    <input id="setting_{{ $setting->key }}" type="number" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}"
                           min="0.0001" max="1000000" step="any" inputmode="decimal"
                           class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                @elseif($setting->type === 'richtext')
                    <textarea id="setting_{{ $setting->key }}" name="{{ $setting->key }}" class="ck-setting">{{ old($setting->key, $setting->value) }}</textarea>
                @elseif($setting->type === 'textarea')
                    <textarea id="setting_{{ $setting->key }}" name="{{ $setting->key }}" rows="3"
                              class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                                     focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-y"
                    >{{ old($setting->key, $setting->value) }}</textarea>
                @elseif($setting->type === 'boolean')
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="{{ $setting->key }}" value="0">
                        <input id="setting_{{ $setting->key }}" type="checkbox" name="{{ $setting->key }}" value="1"
                               class="w-4 h-4 accent-violet-500"
                               @checked(old($setting->key, $setting->value))>
                        <span class="text-sm text-gray-300">Enabled</span>
                    </label>
                @else
                    <input id="setting_{{ $setting->key }}" type="text" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}"
                           class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                                  focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                @endif
                @if($setting->key === 'contact_map_location')
                    <p class="mt-2 text-xs text-gray-400">Enter the exact hotel address or its latitude and longitude. Leave blank to use the hotel name and contact address.</p>
                @elseif($setting->key === 'contact_email')
                    <p class="mt-2 text-xs text-gray-400">Every new enquiry is emailed to each address listed here. Enter up to 20 addresses, separated by commas or new lines. Duplicate addresses receive one notification. These are also the website's contact email addresses.</p>
                    @if(in_array(config('mail.default'), ['log', 'array'], true))
                        <p class="mt-2 text-xs text-amber-300">Email delivery is not enabled on this server yet. Configure SMTP to send enquiry notifications to these inboxes.</p>
                    @endif
                @endif
                @error($setting->key)
                    <p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>
                @enderror
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="mt-6 flex gap-3">
        <button type="submit" class="px-8 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            Save All Settings
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Tab switching
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.getElementById('settings-active-tab').value = btn.dataset.tab;
            document.querySelectorAll('.settings-panel').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.settings-tab-btn').forEach(b => {
                b.classList.remove('bg-violet-600', 'border-violet-600', 'text-white');
                b.classList.add('bg-white/5', 'border-white/10', 'text-gray-400');
            });
            document.getElementById('tab-' + btn.dataset.tab)?.classList.remove('hidden');
            btn.classList.add('bg-violet-600', 'border-violet-600', 'text-white');
            btn.classList.remove('bg-white/5', 'border-white/10', 'text-gray-400');
        });
    });

    // CKEditor for richtext settings
    if (window.CKEDITOR) {
    const { ClassicEditor, Essentials, Bold, Italic, Paragraph, List, Link, Heading } = CKEDITOR;
    document.querySelectorAll('.ck-setting').forEach(el => {
        ClassicEditor.create(el, {
            plugins: [Essentials, Bold, Italic, Paragraph, List, Link, Heading],
            toolbar: ['heading','|','bold','italic','|','bulletedList','numberedList','|','link'],
        }).then(editor => {
            editor.ui.view.editable.element.setAttribute('aria-labelledby', 'label_' + el.name);
            document.getElementById('label_' + el.name)?.addEventListener('click', () => editor.editing.view.focus());
        }).catch(() => { el.hidden = false; });
    });
    }
</script>
@endpush

@endsection
