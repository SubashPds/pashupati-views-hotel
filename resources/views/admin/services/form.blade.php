@extends('layouts.admin')
@section('title', $service->exists ? 'Edit Service' : 'Add Service')
@section('page_title', $service->exists ? 'Edit Service' : 'Add Service')

@section('content')
<div class="max-w-xl">
<form method="POST"
      action="{{ $service->exists ? route('admin.services.update', $service) : route('admin.services.store') }}"
      class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
    @csrf
    @if($service->exists) @method('PUT') @endif

    <x-admin.field label="Title" name="title" :value="$service->title" required />
    <x-admin.field label="Icon (emoji)" name="icon" :value="$service->icon" hint="e.g. ✈️ 🚗 🗺️" />
    <x-admin.field label="Description" name="description" type="textarea" :value="$service->description" />
    <x-admin.field label="Price Label" name="price_label" :value="$service->price_label ?? 'Price on request'" />
    <x-admin.field label="Sort Order" name="sort_order" type="number" :value="$service->sort_order ?? 0" />

    <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            {{ $service->exists ? 'Update' : 'Create' }}
        </button>
        <a href="{{ route('admin.services.index') }}" class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm rounded-xl transition-colors">Cancel</a>
    </div>
</form>
</div>
@endsection
