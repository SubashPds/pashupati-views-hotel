<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('sort_order')->get();
        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.form', ['testimonial' => new Testimonial()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'author_date' => 'nullable|string|max:100',
            'rating'      => 'required|integer|min:1|max:5',
            'review'      => 'required|string',
            'tag'         => 'nullable|string|max:100',
        ]);

        Testimonial::create(array_merge($request->only('author_name', 'author_date', 'rating', 'review', 'tag', 'sort_order'), ['is_active' => true]));
        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial added.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.form', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'author_name' => 'required|string|max:255',
            'author_date' => 'nullable|string|max:100',
            'rating'      => 'required|integer|min:1|max:5',
            'review'      => 'required|string',
            'tag'         => 'nullable|string|max:100',
        ]);

        $testimonial->update($request->only('author_name', 'author_date', 'rating', 'review', 'tag', 'sort_order'));
        return redirect()->route('admin.testimonials.index')->with('success', 'Testimonial updated.');
    }

    public function toggleStatus(Testimonial $testimonial)
    {
        $testimonial->update(['is_active' => ! $testimonial->is_active]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();
        return back()->with('success', 'Testimonial deleted.');
    }
}
