{{-- Reusable form field macro --}}
@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'hint' => null])

<div>
    <label for="{{ $name }}" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
        {{ $label }} @if($required)<span class="text-red-400">*</span>@endif
    </label>

    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4"
                  class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                         focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-y"
        >{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}"
               class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500
                      focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all"
        >
    @endif

    @error($name)
        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
    @enderror

    @if($hint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif
</div>
