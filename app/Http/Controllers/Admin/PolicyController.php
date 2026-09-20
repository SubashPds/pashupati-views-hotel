<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PolicyController extends Controller
{
    public function index()
    {
        $policies = Policy::all()->keyBy('category');
        return view('admin.policies.index', compact('policies'));
    }

    public function edit(Policy $policy)
    {
        return view('admin.policies.form', compact('policy'));
    }

    public function update(Request $request, Policy $policy): RedirectResponse
    {
        // Handle status toggle
        if ($request->has('is_active') && $request->only('is_active') === ['is_active' => 0] || $request->only('is_active') === ['is_active' => 1]) {
            // When only is_active is sent (from the toggle button), don't validate other fields
            $policy->update(['is_active' => (bool) $request->input('is_active')]);
            return back()->with('success', 'Status updated.');
        }

        // Normal form submission - validate title and description
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $policy->update($request->only('title', 'description', 'is_active'));

        return redirect()->route('admin.policies.index')->with('success', $policy->title . ' updated successfully.');
    }
}
