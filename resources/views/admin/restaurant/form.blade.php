@extends('layouts.admin')
@section('title', 'Restaurant Management')
@section('page_title', 'Restaurant Management')
@section('breadcrumb', 'Admin / Restaurant')

@section('content')
<form method="POST" action="{{ route('admin.restaurant.update') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="min-w-0 space-y-6 lg:col-span-2">
            <section class="space-y-5 rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-6" aria-labelledby="restaurant-details">
                <h2 id="restaurant-details" class="border-b border-white/8 pb-3 text-sm font-semibold text-gray-200">Restaurant details</h2>
                <x-admin.field label="Restaurant name" name="name" :value="$restaurant->name" required maxlength="255" />
                <x-admin.field label="Description" name="description" type="textarea" :value="$restaurant->description" rows="3" maxlength="1000" hint="A short introduction displayed at the top of the restaurant page." />
                <x-admin.field label="Overview" name="overview" type="textarea" :value="$restaurant->overview" rows="6" maxlength="10000" hint="Describe the dining experience. Paragraphs are preserved." />
                <x-admin.field label="Cuisine types" name="cuisines_raw" type="textarea" :value="implode(PHP_EOL, $restaurant->cuisines ?? [])" rows="4" maxlength="2000" hint="Enter one cuisine per line." />
                <x-admin.field label="Special / featured dishes" name="featured_dishes_raw" type="textarea" :value="implode(PHP_EOL, $restaurant->featured_dishes ?? [])" rows="5" maxlength="5000" hint="Enter one featured dish per line, with an optional short description. The full food menu is managed as an image." />
            </section>

            <section class="space-y-5 rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-6" aria-labelledby="restaurant-hours">
                <h2 id="restaurant-hours" class="border-b border-white/8 pb-3 text-sm font-semibold text-gray-200">Opening hours & meal timings</h2>
                <p class="text-xs leading-relaxed text-gray-400">Use local hotel time. Complete both times in each pair, or leave both blank to hide it. An end time earlier than the start time means the following day; equal times mean 24 hours.</p>
                @foreach(['Opening hours' => ['opening', 'closing'], 'Breakfast' => ['breakfast_start', 'breakfast_end'], 'Lunch' => ['lunch_start', 'lunch_end'], 'Dinner' => ['dinner_start', 'dinner_end']] as $label => [$start, $end])
                <fieldset class="min-w-0">
                    <legend class="mb-3 text-sm font-medium text-gray-200">{{ $label }}</legend>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <x-admin.field :label="$label === 'Opening hours' ? 'Opens' : 'Starts'" :name="$start . '_time'" type="time" :value="substr($restaurant->{$start . '_time'} ?? '', 0, 5)" />
                        <x-admin.field :label="$label === 'Opening hours' ? 'Closes' : 'Ends'" :name="$end . '_time'" type="time" :value="substr($restaurant->{$end . '_time'} ?? '', 0, 5)" />
                    </div>
                </fieldset>
                @endforeach
            </section>
        </div>

        <div class="min-w-0 space-y-6">
            <section class="space-y-4 rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-6" aria-labelledby="restaurant-menu">
                <h2 id="restaurant-menu" class="text-sm font-semibold text-gray-200">Food menu image</h2>
                <p class="text-xs leading-relaxed text-gray-400">Upload the complete menu as one clear image. Visitors can open the original image to zoom in. A new upload replaces the current menu.</p>
                @include('admin.restaurant.upload', ['name' => 'menu_image', 'label' => 'Upload food menu image', 'current' => $restaurant->menu_image])
                @if($restaurant->menu_image)
                <label class="flex items-center gap-2 text-sm text-red-300">
                    <input type="checkbox" name="remove_menu_image" value="1" @checked(old('remove_menu_image'))> Remove menu image on save
                </label>
                @endif
                @error('menu_image') <p class="text-xs text-red-400">{{ $message }}</p> @enderror
            </section>

            <section class="space-y-4 rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-6" aria-labelledby="restaurant-visibility">
                <h2 id="restaurant-visibility" class="text-sm font-semibold text-gray-200">Visibility</h2>
                <input type="hidden" name="is_active" value="0">
                <label class="flex items-center gap-3 text-sm text-gray-300">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $restaurant->is_active))> Publish restaurant content
                </label>
                <p class="text-xs text-gray-400">Leave unchecked to keep restaurant content private while editing.</p>
                <a href="{{ route('restaurant') }}" target="_blank" rel="noopener" class="inline-block text-sm text-violet-300 hover:text-violet-200">View restaurant page ↗</a>
            </section>
        </div>
    </div>

    <section class="space-y-5 rounded-2xl border border-white/8 bg-white/5 p-5 sm:p-6" aria-labelledby="restaurant-gallery">
        <h2 id="restaurant-gallery" class="border-b border-white/8 pb-3 text-sm font-semibold text-gray-200">Restaurant gallery</h2>
        <p class="text-xs text-gray-400">Add up to 20 photos per save. Use captions to describe each photo. Replacements and removals take effect when you save.</p>
        @if($restaurant->exists)
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($restaurant->images as $image)
            @php($index = $loop->index)
            <div class="min-w-0 space-y-3 rounded-xl border border-white/10 p-4">
                <input type="hidden" name="images[{{ $index }}][id]" value="{{ $image->id }}">
                @include('admin.restaurant.upload', ['name' => "images[$index][replacement]", 'label' => 'Replace gallery image ' . $loop->iteration, 'current' => $image->image_path])
                <label for="image-caption-{{ $image->id }}" class="block text-xs font-semibold text-gray-400">Photo caption</label>
                <input id="image-caption-{{ $image->id }}" type="text" name="images[{{ $index }}][caption]" value="{{ old('images.' . $index . '.caption', $image->caption) }}" maxlength="255" class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white focus:outline-none focus:ring-2 focus:ring-violet-400">
                <label class="flex items-center gap-2 text-sm text-red-300">
                    <input type="checkbox" name="images[{{ $index }}][remove]" value="1" @checked(old('images.' . $index . '.remove'))> Remove gallery image {{ $loop->iteration }} on save
                </label>
            </div>
            @endforeach
        </div>
        @endif
        @include('admin.restaurant.upload', ['name' => 'gallery_images[]', 'label' => 'Upload restaurant gallery images', 'multiple' => true, 'current' => null])
        <p class="text-xs text-gray-400">Select more photos to add to your current selection. After a validation error, select your uploads again.</p>
    </section>

    <button type="submit" class="rounded-xl bg-violet-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-violet-500 focus-visible:outline-2 focus-visible:outline-violet-400">Save restaurant</button>
</form>
@endsection
