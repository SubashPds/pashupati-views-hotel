@extends('layouts.admin')
@section('title', $room->exists ? 'Edit Room' : 'Add Room')
@section('page_title', $room->exists ? 'Edit Room' : 'Add Room')
@section('breadcrumb')
    <a href="{{ route('admin.rooms.index') }}" class="hover:text-gray-300">Rooms</a> /
    {{ $room->exists ? $room->name : 'New' }}
@endsection

@push('head')
{{-- CKEditor 5 CDN --}}
<script nonce="{{ Vite::cspNonce() }}" src="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.umd.js"></script>
<link  href="https://cdn.ckeditor.com/ckeditor5/43.3.1/ckeditor5.css" rel="stylesheet">
@endpush

@section('content')

<form method="POST"
      action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}"
      enctype="multipart/form-data"
      class="space-y-8">
    @csrf
    @if($room->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: main fields --}}
        <div class="lg:col-span-2 space-y-6">

            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
                <h3 class="text-sm font-semibold text-gray-200 mb-1">Basic Information</h3>

                <x-admin.field label="Room Name" name="name" :value="$room->name" required />

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Category <span class="text-red-400">*</span></label>
                        <select name="category" class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all">
                            @foreach(['deluxe','premium','suite'] as $cat)
                                <option value="{{ $cat }}" {{ old('category', $room->category) === $cat ? 'selected' : '' }} class="bg-gray-900">{{ ucfirst($cat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-admin.field label="Tagline" name="tagline" :value="$room->tagline" hint="e.g. A QUIET RETREAT" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.field label="Price per Night (NPR)" name="price_per_night" type="number" step="0.01" min="0" max="99999999.99" :value="$room->price_per_night" required />
                    <x-admin.field label="Max Guests" name="max_guests" type="number" :value="$room->max_guests ?? 2" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.field label="Room Size (m²)" name="size_sqm" type="number" :value="$room->size_sqm" />
                    <x-admin.field label="Bed Type" name="bed_type" :value="$room->bed_type" hint="e.g. 1 king bed" />
                </div>

                <x-admin.field label="Short Description" name="short_description" type="textarea" :value="$room->short_description" hint="Brief one-liner shown on card (max 500 chars)" />
            </div>

            {{-- CKEditor Description --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Full Description</label>
                <textarea id="description" name="description" class="ck-editor-field">{{ old('description', $room->description) }}</textarea>
            </div>

            {{-- Amenities --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Amenities</label>
                <p class="text-xs text-gray-500 mb-3">One amenity per line (e.g. Wi-Fi, Air conditioning…)</p>
                <textarea name="amenities_raw" rows="6"
                          class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 font-mono
                                 focus:outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition-all resize-y"
                >{{ old('amenities_raw', is_array($room->amenities) ? implode("\n", $room->amenities) : '') }}</textarea>
            </div>
        </div>

        {{-- Right: images & meta --}}
        <div class="space-y-6">

            {{-- Cover Image --}}
            <div data-room-image-upload class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <h3 class="text-sm font-semibold text-gray-200 mb-4">Cover Image</h3>
                @if($room->cover_image)
                    <img data-saved-cover src="{{ $room->cover_image_url }}" alt="Current cover image" class="w-full h-36 object-contain bg-black/20 rounded-xl mb-3">
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

            {{-- Gallery Images --}}
            <div data-room-image-upload class="p-6 rounded-2xl bg-white/5 border border-white/8">
                <h3 class="text-sm font-semibold text-gray-200 mb-4">Gallery Images</h3>

                @if($room->exists && $room->images->isNotEmpty())
                <div class="grid grid-cols-3 gap-2 mb-4">
                    @foreach($room->images as $img)
                    <div class="relative group">
                        <img src="{{ Storage::url($img->image_path) }}" alt="Gallery" class="w-full h-20 object-cover rounded-lg">
                        <button type="submit" form="delete-room-image-{{ $img->id }}" aria-label="Remove gallery image {{ $loop->iteration }}"
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

            {{-- Meta --}}
            <div class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-4">
                <h3 class="text-sm font-semibold text-gray-200">Meta</h3>
                <x-admin.field label="Sort Order" name="sort_order" type="number" :value="$room->sort_order ?? 0" />
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 accent-violet-500"
                           {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-300">Active (visible on website)</span>
                </label>
            </div>

            {{-- Save --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
                    {{ $room->exists ? 'Update Room' : 'Create Room' }}
                </button>
                <a href="{{ route('admin.rooms.index') }}"
                   class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm font-medium rounded-xl transition-colors">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>

@if($room->exists)
    @foreach($room->images as $img)
    <form id="delete-room-image-{{ $img->id }}" method="POST" action="{{ route('admin.rooms.images.destroy', $img) }}" data-confirm="Remove image?">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
@endif

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}" type="module">
    const { ClassicEditor, Autoformat, Bold, Italic, Underline, Essentials, Heading, Link, List, Paragraph, Table } = CKEDITOR;
    ClassicEditor.create(document.querySelector('#description'), {
        plugins: [Essentials, Bold, Italic, Underline, Heading, List, Link, Paragraph, Autoformat, Table],
        toolbar: ['heading','|','bold','italic','underline','|','bulletedList','numberedList','|','link','insertTable','|','undo','redo'],
    });
</script>
@endpush

@endsection
