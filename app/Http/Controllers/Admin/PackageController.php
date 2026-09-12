<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('sort_order')->get();
        return view('admin.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.packages.form', ['package' => new Package()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatePackage($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('packages', 'public');
        }

        $data['includes']   = $this->parseLines($request->input('includes_raw'));
        $data['highlights'] = $this->parseLines($request->input('highlights_raw'));

        Package::create($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validatePackage($request, $package->id);

        if ($request->hasFile('cover_image')) {
            if ($package->cover_image) {
                Storage::disk('public')->delete($package->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('packages', 'public');
        }

        $data['includes']   = $this->parseLines($request->input('includes_raw'));
        $data['highlights'] = $this->parseLines($request->input('highlights_raw'));

        $package->update($data);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => ! $package->is_active]);
        return back()->with('success', 'Package status updated.');
    }

    public function destroy(Package $package)
    {
        if ($package->cover_image) {
            Storage::disk('public')->delete($package->cover_image);
        }
        $package->delete();
        return back()->with('success', 'Package deleted.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function validatePackage(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'              => 'required|string|max:255',
            'tagline'           => 'nullable|string|max:255',
            'badge'             => 'nullable|string|max:100',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'price_label'       => 'nullable|string|max:255',
            'price_from'        => 'nullable|numeric|min:0',
            'duration'          => 'nullable|string|max:100',
            'min_guests'        => 'nullable|integer|min:1',
            'max_guests'        => ['nullable', 'integer', 'min:1', ...($request->filled('min_guests') ? ['gte:min_guests'] : [])],
            'cover_image'       => 'nullable|image|max:4096',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer',
        ]);
    }

    /** Convert textarea (one item per line) → JSON array */
    private function parseLines(?string $raw): array
    {
        if (empty($raw)) {
            return [];
        }
        return array_values(array_filter(array_map('trim', explode("\n", $raw))));
    }
}
