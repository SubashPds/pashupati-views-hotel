<div data-room-image-upload class="min-w-0 space-y-3">
    @if($current ?? null)
        <img data-saved-cover src="{{ Storage::disk('public')->url($current) }}" alt="{{ $label }}" class="h-40 w-full rounded-xl bg-black/20 object-contain">
    @endif
    <div data-upload-preview hidden>
        <div data-preview-images class="grid grid-cols-1 gap-3"></div>
        <button type="button" data-clear-upload class="mt-2 text-xs text-violet-300 hover:text-violet-200">Clear selection</button>
    </div>
    <p data-upload-status class="sr-only" role="status"></p>
    <label class="flex min-h-24 flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-white/15 p-4 text-center hover:border-violet-400/50 focus-within:ring-2 focus-within:ring-violet-400">
        <span class="text-sm text-gray-300">{{ $label }}</span>
        <span class="text-xs text-gray-400">JPG, PNG or WEBP · Up to 4 MB per image</span>
        <input type="file" name="{{ $name }}" class="sr-only" accept="image/jpeg,image/png,image/webp" @if($multiple ?? false) multiple @endif>
    </label>
</div>
