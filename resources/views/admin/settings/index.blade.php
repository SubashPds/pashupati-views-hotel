@extends('layouts.admin')
@section('title','Site Settings')
@section('page_title','Site Settings')
@section('breadcrumb','Admin / Settings')

@push('head')
<script src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
<link  href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" rel="stylesheet">
@endpush

@section('content')

<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf @method('POST')

    {{-- Tab navigation --}}
    @php
        $groups = $settings->keys()->toArray();
        $groupLabels = [
            'general'      => '🌐 General',
            'hero'         => '🦸 Hero',
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

    <div class="mb-6 flex flex-wrap gap-2" id="settings-tabs">
        @foreach($settings->keys() as $i => $group)
        <button type="button" data-tab="{{ $group }}"
                class="settings-tab-btn px-3.5 py-1.5 text-xs font-medium rounded-lg border transition-colors
                       {{ $i === 0 ? 'bg-violet-600 border-violet-600 text-white' : 'bg-white/5 border-white/10 text-gray-400 hover:text-white' }}">
            {{ $groupLabels[$group] ?? ucfirst($group) }}
        </button>
        @endforeach
    </div>

    @foreach($settings as $group => $items)
    <div id="tab-{{ $group }}" class="settings-panel {{ $loop->first ? '' : 'hidden' }}">
        <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
            <h3 class="text-sm font-semibold text-gray-200 mb-2">{{ $groupLabels[$group] ?? ucfirst($group) }}</h3>

            @foreach($items->sortBy('sort_order') as $setting)
            <div>
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                    {{ $setting->label }}
                </label>

                @if($setting->type === 'richtext')
                    <textarea id="ck_{{ $setting->key }}" name="{{ $setting->key }}" class="ck-setting">{{ $setting->value }}</textarea>
                @elseif($setting->type === 'textarea')
                    <textarea name="{{ $setting->key }}" rows="3"
                              class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                                     focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-y"
                    >{{ $setting->value }}</textarea>
                @elseif($setting->type === 'boolean')
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="{{ $setting->key }}" value="0">
                        <input type="checkbox" name="{{ $setting->key }}" value="1"
                               class="w-4 h-4 accent-violet-500"
                               {{ $setting->value ? 'checked' : '' }}>
                        <span class="text-sm text-gray-300">Enabled</span>
                    </label>
                @else
                    <input type="text" name="{{ $setting->key }}" value="{{ $setting->value }}"
                           class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                                  focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                @endif
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
    const { ClassicEditor, Essentials, Bold, Italic, Paragraph, List, Link, Heading } = CKEDITOR;
    document.querySelectorAll('.ck-setting').forEach(el => {
        ClassicEditor.create(el, {
            plugins: [Essentials, Bold, Italic, Paragraph, List, Link, Heading],
            toolbar: ['heading','|','bold','italic','|','bulletedList','numberedList','|','link'],
        });
    });
</script>
@endpush

@endsection
