<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageImage;
use App\Services\MediaFiles;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

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
        $data['slug'] = Str::slug($data['name']);
        $this->ensureSlugAvailable($data['slug']);

        $data['includes']   = $this->parseLines($request->input('includes_raw'));
        $data['highlights'] = $this->parseLines($request->input('highlights_raw'));

        try {
            MediaFiles::transaction(function (MediaFiles $files) use ($request, $data) {
                if ($request->hasFile('cover_image')) {
                    $data['cover_image'] = $files->store($request->file('cover_image'), 'packages', preview: true);
                }
                $package = new Package($data);
                if (! $package->save()) {
                    throw new RuntimeException('The package could not be saved.');
                }
                $this->handleGalleryUploads($request, $package, $files);
            });
        } catch (UniqueConstraintViolationException $exception) {
            // A competing request may claim the slug after validation. Check
            // after rollback and upload cleanup; preserve unrelated errors.
            $this->ensureSlugAvailable($data['slug']);

            throw $exception;
        }

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(Package $package)
    {
        $package->load('images');
        return view('admin.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $data = $this->validatePackage($request, $package->id);

        $data['includes']   = $this->parseLines($request->input('includes_raw'));
        $data['highlights'] = $this->parseLines($request->input('highlights_raw'));

        MediaFiles::transaction(function (MediaFiles $files) use ($request, $package, $data) {
            $package = Package::lockForUpdate()->findOrFail($package->id);
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $files->store($request->file('cover_image'), 'packages', preview: true);
                $files->deleteAfterCommit($package->cover_image);
            }
            if (! $package->update($data)) {
                throw new RuntimeException('The package could not be saved.');
            }
            $this->handleGalleryUploads($request, $package, $files);
        });

        return redirect()->route('admin.packages.index')->with('success', 'Package updated.');
    }

    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => ! $package->is_active]);
        return back()->with('success', 'Package status updated.');
    }

    public function destroy(Package $package)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($package) {
            $package = Package::lockForUpdate()->findOrFail($package->id);
            $files->deleteAfterCommit($package->cover_image);
            $package->images()->each(fn ($image) => $files->deleteAfterCommit($image->image_path));
            if (! $package->delete()) {
                throw new RuntimeException('The package could not be deleted.');
            }
        });
        return back()->with('success', 'Package deleted.');
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    private function ensureSlugAvailable(string $slug): void
    {
        if (Package::where('slug', $slug)->useWritePdo()->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A package with this name or a similar name already exists. Please choose a different name.',
            ]);
        }
    }

    public function destroyImage(PackageImage $image)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($image) {
            $files->deleteAfterCommit($image->image_path);
            if (! $image->delete()) {
                throw new RuntimeException('The image could not be deleted.');
            }
        });
        return back()->with('success', 'Image removed.');
    }

    private function handleGalleryUploads(Request $request, Package $package, MediaFiles $files): void
    {
        $lastOrder = $package->images()->max('sort_order') ?? 0;
        foreach ($request->file('gallery_images') ?? [] as $i => $file) {
            $image = $package->images()->create([
                'image_path' => $files->store($file, 'packages/gallery'),
                'sort_order' => $lastOrder + $i + 1,
            ]);
            if (! $image->exists) {
                throw new RuntimeException('The image could not be saved.');
            }
        }
    }

    private function validatePackage(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
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
            'cover_image'       => 'nullable|image|max:6096',
            'gallery_images'    => 'nullable|array|max:20',
            'gallery_images.*'  => 'required|image|mimes:jpg,jpeg,png,webp|max:6096',
            'is_active'         => 'nullable|boolean',
            'sort_order'        => 'nullable|integer',
        ]);
        unset($validated['gallery_images']);
        foreach (['min_guests' => 1, 'sort_order' => 0] as $field => $default) {
            if (array_key_exists($field, $validated)) {
                $validated[$field] ??= $default;
            }
        }
        return $validated;
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
