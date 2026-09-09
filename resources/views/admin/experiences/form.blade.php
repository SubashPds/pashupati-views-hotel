@extends('layouts.admin')
@section('title', $experience->exists ? 'Edit Experience' : 'Add Experience')
@section('page_title', $experience->exists ? 'Edit Experience' : 'Add Experience')

@section('content')
<div class="max-w-xl">
<form method="POST"
      action="{{ $experience->exists ? route('admin.experiences.update', $experience) : route('admin.experiences.store') }}"
      class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
    @csrf
    @if($experience->exists) @method('PUT') @endif

    <x-admin.field label="Title" name="title" :value="$experience->title" required />
    <x-admin.field label="Icon (emoji)" name="icon" :value="$experience->icon" hint="e.g. 🛕 🌿 📶" />
    <x-admin.field label="Description" name="description" type="textarea" :value="$experience->description" />
    <x-admin.field label="Sort Order" name="sort_order" type="number" :value="$experience->sort_order ?? 0" />

    <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            {{ $experience->exists ? 'Update' : 'Create' }}
        </button>
        <a href="{{ route('admin.experiences.index') }}" class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm rounded-xl transition-colors">Cancel</a>
    </div>
</form>
</div>
@endsection
