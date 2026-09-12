@props(['label', 'name', 'type' => 'text', 'value' => '', 'required' => false, 'hint' => null])
@php
    $fieldId = $attributes->get('id', $name);
    $fieldClass = 'w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all';
    $describedBy = trim(($hint ? $fieldId . '-hint ' : '') . ($errors->has($name) ? $fieldId . '-error' : ''));
@endphp
<div>
    <label for="{{ $fieldId }}" class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
        {{ $label }} @if($required)<span class="text-red-400" aria-hidden="true">*</span>@endif
    </label>
    @if($type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $name }}" @required($required)
                  @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
                  @if($errors->has($name)) aria-invalid="true" @endif
                  {{ $attributes->except('id')->merge(['rows' => 4, 'class' => $fieldClass . ' resize-y']) }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ old($name, $value) }}" @required($required)
               @if($describedBy) aria-describedby="{{ $describedBy }}" @endif
               @if($errors->has($name)) aria-invalid="true" @endif
               {{ $attributes->except('id')->merge(['class' => $fieldClass]) }}>
    @endif
    @error($name)<p id="{{ $fieldId }}-error" class="mt-1.5 text-xs text-red-400">{{ $message }}</p>@enderror
    @if($hint)<p id="{{ $fieldId }}-hint" class="mt-1 text-xs text-gray-400">{{ $hint }}</p>@endif
</div>
