@extends('layouts.admin')
@section('title', isset($package->id) ? 'Edit Package' : 'New Package')
@section('page_title', isset($package->id) ? 'Edit Package' : 'Create Package')
@section('breadcrumb', 'Admin / Packages / ' . (isset($package->id) ? 'Edit' : 'New'))

@section('content')

@php
    $isEdit  = isset($package->id);
    $action  = $isEdit ? route('admin.packages.update', $package) : route('admin.packages.store');
    $method  = $isEdit ? 'PUT' : 'POST';
    $includesRaw  = is_array($package->includes)  ? implode("\n", $package->includes)  : '';
    $highlightsRaw= is_array($package->highlights) ? implode("\n", $package->highlights) : '';
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left: package details --}}
        <div class="min-w-0 lg:col-span-2 space-y-6">
            {{-- ── Basic Info ──────────────────────────────────────────────────────── --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
                <h3 class="text-sm font-semibold text-gray-200 pb-3 border-b border-white/8">Basic Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-admin.field label="Package Name *" name="name" :value="old('name', $package->name)" />
                    <x-admin.field label="Tagline" name="tagline" :value="old('tagline', $package->tagline)"
                                   hint="e.g. SPIRITUAL IMMERSION" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <x-admin.field label="Badge Label" name="badge" :value="old('badge', $package->badge)"
                                   hint="e.g. Most Popular, Best Value, Romantic" />
                    <x-admin.field label="Duration" name="duration" :value="old('duration', $package->duration)"
                                   hint="e.g. 2 Nights / 3 Days" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Short Description</label>
                    <textarea name="short_description" rows="2" maxlength="500"
                              class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 resize-none transition-all">{{ old('short_description', $package->short_description) }}</textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Full Description</label>
                    <textarea name="description" rows="6"
                              class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 resize-y transition-all">{{ old('description', $package->description) }}</textarea>
                </div>
            </div>

            {{-- ── Pricing & Guests ──────────────────────────────────────────────── --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
                <h3 class="text-sm font-semibold text-gray-200 pb-3 border-b border-white/8">Pricing & Guests</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    <x-admin.field label="Price Label" name="price_label" :value="old('price_label', $package->price_label)"
                                   hint="e.g. NPR 12,000 / night" />
                    <x-admin.field label="Price From (numeric)" name="price_from" type="number" step="0.01" min="0" max="99999999.99"
                                   :value="old('price_from', $package->price_from)"
                                   hint="Base price in NPR; used when Price Label is blank." />
                    <div class="grid grid-cols-2 gap-3">
                        <x-admin.field label="Min Guests" name="min_guests" type="number" min="1" max="127"
                                       :value="old('min_guests', $package->min_guests ?? 1)" hint="Leave blank for 1 guest." />
                        <x-admin.field label="Max Guests" name="max_guests" type="number" min="1" max="127"
                                       :value="old('max_guests', $package->max_guests)" hint="Leave blank = unlimited" />
                    </div>
                </div>
            </div>

            {{-- ── Includes & Highlights ────────────────────────────────────────── --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
                <h3 class="text-sm font-semibold text-gray-200 pb-3 border-b border-white/8">What's Included & Highlights</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                            What's Included
                            <span class="normal-case text-gray-500 font-normal ml-1">(one item per line)</span>
                        </label>
                        <textarea name="includes_raw" rows="8" placeholder="Daily breakfast&#10;Airport pickup&#10;Guided temple tour"
                                  class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 resize-y transition-all font-mono">{{ old('includes_raw', $includesRaw) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">
                            Highlights
                            <span class="normal-case text-gray-500 font-normal ml-1">(one item per line)</span>
                        </label>
                        <textarea name="highlights_raw" rows="8" placeholder="Steps from Pashupatinath&#10;Sacred evening Aarati&#10;Spiritual walks"
                                  class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-violet-500 resize-y transition-all font-mono">{{ old('highlights_raw', $highlightsRaw) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: images, settings, and save controls --}}
        <div class="min-w-0 space-y-6">
            {{-- Cover Image --}}
            <div data-room-image-upload class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <h3 class="text-sm font-semibold text-gray-200 mb-4">Cover Image</h3>
                @if($package->cover_image)
                    <img data-saved-cover src="{{ $package->cover_image_url }}" alt="Current cover image" class="w-full h-36 object-contain bg-black/20 rounded-xl mb-3">
                @endif
                <div data-upload-preview hidden class="mb-3">
                    <div data-preview-images class="grid grid-cols-1 gap-3"></div>
                    <button type="button" data-clear-upload class="mt-2 text-xs text-violet-300 hover:text-violet-200">Clear selection</button>
                </div>
                <p data-upload-status class="sr-only" role="status"></p>
                <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-white/15 rounded-xl cursor-pointer hover:border-violet-400/50 focus-within:ring-2 focus-within:ring-violet-400 transition-colors">
                    <span class="text-sm text-gray-400">Click to upload cover image</span>
                    <span class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP (max 4MB)</span>
                    <input type="file" name="cover_image" class="sr-only" accept="image/jpeg,image/png,image/webp">
                </label>
            </div>

            @error('cover_image') <p class="text-sm text-red-300">{{ $message }}</p> @enderror

            {{-- Gallery Images --}}
            <div data-room-image-upload class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <h3 class="text-sm font-semibold text-gray-200 mb-4">Gallery Images</h3>

                @if($package->exists && $package->images->isNotEmpty())
                <div class="grid grid-cols-3 gap-2 mb-4">
                    @foreach($package->images as $img)
                    <div class="relative group">
                        <img src="{{ Storage::url($img->image_path) }}" alt="Gallery" class="w-full h-20 object-cover rounded-lg">
                        <button type="submit" form="delete-package-image-{{ $img->id }}" aria-label="Remove gallery image {{ $loop->iteration }}"
                                class="absolute top-1 right-1 w-6 h-6 bg-red-500 hover:bg-red-600 text-white rounded-full text-xs flex items-center justify-center">✕</button>
                    </div>
                    @endforeach
                </div>
                @endif

                <div data-upload-preview hidden class="mb-4">
                    <p class="mb-2 text-xs font-medium text-gray-400">Selected photos</p>
                    <div data-preview-images class="grid grid-cols-2 gap-3"></div>
                    <button type="button" data-clear-upload class="mt-2 text-xs text-violet-300 hover:text-violet-200">Clear selection</button>
                </div>
                <p data-upload-status class="sr-only" role="status"></p>

                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-white/15 rounded-xl cursor-pointer hover:border-violet-400/50 focus-within:ring-2 focus-within:ring-violet-400 transition-colors">
                    <span class="text-sm text-gray-400">Upload gallery photos</span>
                    <span class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · Max 4 MB per image</span>
                    <input type="file" name="gallery_images[]" class="sr-only" accept="image/jpeg,image/png,image/webp" multiple>
                </label>
                <p class="mt-2 text-xs text-gray-400">Select more photos to add to your current selection.</p>
            </div>

            @error('gallery_images') <p class="text-sm text-red-300">{{ $message }}</p> @enderror
            @foreach($errors->get('gallery_images.*') as $messages)
                @foreach($messages as $message)
                    <p class="text-sm text-red-300">{{ $message }}</p>
                @endforeach
            @endforeach

            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
                <h3 class="text-sm font-semibold text-gray-200 pb-3 border-b border-white/8">Settings</h3>
                <div class="space-y-4">
                    <div class="space-y-4">
                        <x-admin.field label="Sort Order" name="sort_order" type="number"
                                       :value="old('sort_order', $package->sort_order ?? 0)" hint="Leave blank for 0." />
                        <div class="flex items-center gap-3 pt-2">
                            <label class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Visibility</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                       {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none peer-checked:bg-violet-600 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-2 text-sm text-gray-300 peer-checked:text-white">Active</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Submit ───────────────────────────────────────────────────────── --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-6 py-2.5 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ $isEdit ? 'Save Changes' : 'Create Package' }}
                </button>
                <a href="{{ route('admin.packages.index') }}"
                   class="px-6 py-2.5 text-sm font-semibold text-gray-400 bg-white/5 hover:bg-white/10 rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

@if($package->exists)
    @foreach($package->images as $img)
    <form id="delete-package-image-{{ $img->id }}" method="POST" action="{{ route('admin.packages.images.destroy', $img) }}" data-confirm="Remove image?">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
@endif


@endsection
