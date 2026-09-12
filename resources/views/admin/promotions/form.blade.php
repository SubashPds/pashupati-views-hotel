@extends('layouts.admin')
@section('title', $promotion->exists ? 'Edit Promotion' : 'Add Promotion')
@section('page_title', $promotion->exists ? 'Edit Promotion' : 'Add Promotion')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $promotion->exists ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" class="max-w-3xl p-6 rounded-2xl bg-white/5 space-y-5">
    @csrf
    @if($promotion->exists) @method('PUT') @endif
    <x-admin.field label="Title" name="title" :value="$promotion->title" required maxlength="180" />
    <x-admin.field label="Small heading / badge" name="label" :value="$promotion->label" maxlength="100" />
    <x-admin.field label="Description" name="description" type="textarea" :value="$promotion->description" maxlength="1500" />
    <div data-offer-image data-saved-url="{{ $promotion->image_url }}">
        <label for="offer_image" class="block mb-2 text-sm text-gray-300">Promotional image</label>
        <div data-offer-preview class="hidden mb-3">
            <img data-offer-preview-image alt="Promotional image preview" class="w-full max-w-md h-48 object-contain rounded-xl bg-black/20">
        </div>
        <input id="offer_image" type="file" name="offer_image" accept="image/jpeg,image/png,image/webp" aria-describedby="offer-image-hint"
               class="block w-full text-sm text-gray-300 file:mr-4 file:rounded-lg file:border-0 file:bg-violet-600 file:px-4 file:py-2 file:text-white">
        <p id="offer-image-hint" class="mt-2 text-xs text-gray-400">JPG, PNG or WEBP, up to 4 MB. The complete image is shown in the popup. Reselect your image if validation fails.</p>
        <button type="button" data-clear-offer-image class="hidden mt-3 text-sm text-violet-300 hover:text-violet-200">Clear selection</button>
        @if($promotion->image_path)
            <label class="mt-3 flex items-center gap-2 text-sm text-gray-300">
                <input type="checkbox" name="remove_offer_image" value="1" @checked(old('remove_offer_image')) class="accent-violet-500">
                Remove saved image when saving
            </label>
        @endif
        @error('offer_image')<p role="alert" class="mt-2 text-sm text-red-300">{{ $message }}</p>@enderror
        <p data-offer-image-status role="status" class="sr-only"></p>
    </div>
    <x-admin.field label="Image description for accessibility" name="image_alt" :value="$promotion->image_alt" maxlength="255" />
    <div class="grid sm:grid-cols-2 gap-5">
        <x-admin.field label="Button text" name="button_text" :value="$promotion->exists ? $promotion->button_text : 'Enquire now'" maxlength="80" hint="Leave blank to hide the button." />
        <x-admin.field label="Button link" name="button_url" :value="$promotion->button_url" maxlength="1000" hint="Blank opens Plan Your Stay. Or use https://…, /blogs, or #contact." />
    </div>
    <x-admin.field label="Sort order (lowest first)" name="sort_order" type="number" :value="$promotion->sort_order ?? 0" required min="0" max="100000" />
    <x-admin.field label="End date (optional)" name="end_date" type="date" :value="$promotion->end_date?->format('Y-m-d')" hint="Visible through this date until midnight Nepal time (Asia/Kathmandu). After that, it is hidden even if active. Leave blank for no expiry." />
    <input type="hidden" name="is_active" value="0">
    <label class="flex gap-3 text-gray-300"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $promotion->exists ? $promotion->is_active : true)) class="accent-violet-500"> Active — show to visitors until the end date</label>
    <div class="flex gap-4">
        <button class="px-6 py-3 bg-violet-600 hover:bg-violet-500 text-white rounded-xl">Save promotion</button>
        <a href="{{ route('admin.promotions.index') }}" class="px-6 py-3 text-gray-300">Cancel</a>
    </div>
</form>
@endsection
