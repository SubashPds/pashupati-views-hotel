<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('sort_order')->get();
        return view('admin.services.index', compact('services'));
    }

    public function create()
    {
        return view('admin.services.form', ['service' => new Service()]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:20',
            'price_label' => 'nullable|string|max:100',
        ]);

        Service::create(array_merge($request->only('title', 'description', 'icon', 'price_label', 'sort_order'), ['is_active' => true]));
        return redirect()->route('admin.services.index')->with('success', 'Service added.');
    }

    public function edit(Service $service)
    {
        return view('admin.services.form', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:20',
            'price_label' => 'nullable|string|max:100',
        ]);

        $service->update($request->only('title', 'description', 'icon', 'price_label', 'sort_order'));
        return redirect()->route('admin.services.index')->with('success', 'Service updated.');
    }

    public function toggleStatus(Service $service)
    {
        $service->update(['is_active' => ! $service->is_active]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('success', 'Service deleted.');
    }
}
