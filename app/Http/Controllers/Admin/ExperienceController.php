<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Http\Request;

class ExperienceController extends Controller
{
    public function index()
    {
        $experiences = Experience::orderBy('sort_order')->get();
        return view('admin.experiences.index', compact('experiences'));
    }

    public function create()
    {
        return view('admin.experiences.form', ['experience' => new Experience()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:20',
        ]);

        Experience::create(array_merge($request->only('title', 'description', 'icon', 'sort_order'), ['is_active' => true]));
        return redirect()->route('admin.experiences.index')->with('success', 'Experience item added.');
    }

    public function edit(Experience $experience)
    {
        return view('admin.experiences.form', compact('experience'));
    }

    public function update(Request $request, Experience $experience)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:20',
        ]);

        $experience->update($request->only('title', 'description', 'icon', 'sort_order'));
        return redirect()->route('admin.experiences.index')->with('success', 'Experience item updated.');
    }

    public function toggleStatus(Experience $experience)
    {
        $experience->update(['is_active' => ! $experience->is_active]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(Experience $experience)
    {
        $experience->delete();
        return back()->with('success', 'Item deleted.');
    }
}
