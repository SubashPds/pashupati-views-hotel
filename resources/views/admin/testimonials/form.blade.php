@extends('layouts.admin')
@section('title', $testimonial->exists ? 'Edit Testimonial' : 'Add Testimonial')
@section('page_title', $testimonial->exists ? 'Edit Testimonial' : 'Add Testimonial')

@section('content')
<div class="max-w-xl">
<form method="POST"
      action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}"
      class="p-6 rounded-2xl bg-white/5 border border-white/8 space-y-5">
    @csrf
    @if($testimonial->exists) @method('PUT') @endif

    <x-admin.field label="Author Name" name="author_name" :value="$testimonial->author_name" required />
    <x-admin.field label="Date Label" name="author_date" :value="$testimonial->author_date" hint="e.g. September 2026" />

    <div>
        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1.5">Rating <span class="text-red-400">*</span></label>
        <select name="rating" class="w-full px-4 py-3 text-sm bg-white/5 border border-white/10 rounded-xl text-white focus:outline-none focus:border-violet-500 transition-all">
            @foreach([5,4,3,2,1] as $r)
                <option value="{{ $r }}" {{ old('rating', $testimonial->rating ?? 5) == $r ? 'selected' : '' }} class="bg-gray-900">
                    {{ str_repeat('★', $r) }} ({{ $r }} stars)
                </option>
            @endforeach
        </select>
    </div>

    <x-admin.field label="Review" name="review" type="textarea" :value="$testimonial->review" required />
    <x-admin.field label="Tag" name="tag" :value="$testimonial->tag" hint="e.g. Verified Guest" />
    <x-admin.field label="Sort Order" name="sort_order" type="number" :value="$testimonial->sort_order ?? 0" />

    <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 py-3 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold rounded-xl transition-colors">
            {{ $testimonial->exists ? 'Update' : 'Create' }}
        </button>
        <a href="{{ route('admin.testimonials.index') }}" class="flex-1 py-3 text-center bg-white/8 hover:bg-white/15 text-gray-300 text-sm rounded-xl transition-colors">Cancel</a>
    </div>
</form>
</div>
@endsection
