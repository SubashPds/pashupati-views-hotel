<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        // Browser toggle forms also submit CSRF and method-spoofing fields.
        if (array_keys($request->except(['_token', '_method'])) === ['is_active']) {
            $request->validate(['is_active' => 'required|boolean']);
            $policy->update(['is_active' => $request->boolean('is_active')]);

            return back()->with('success', 'Status updated.');
        }

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'sometimes|required|boolean',
        ]);

        if (array_key_exists('is_active', $validated)) {
            $validated['is_active'] = $request->boolean('is_active');
        }
        $policy->update($validated);

        return redirect()->route('admin.policies.index')->with('success', $policy->title . ' updated successfully.');
    }
}
