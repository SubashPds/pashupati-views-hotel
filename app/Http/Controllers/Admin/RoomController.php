<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomImage;
use App\Services\ImagePreview;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::orderBy('sort_order')->get();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        return view('admin.rooms.form', ['room' => new Room()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validate($request);

        $slug = Str::slug($validated['name']);
        if (Room::where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A room with this name or a similar name already exists. Please choose a different name.',
            ]);
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('rooms', 'public');
            app(ImagePreview::class)->generate($validated['cover_image']);
        }

        $validated['amenities'] = $this->parseAmenities($request->input('amenities_raw', ''));
        $validated['slug'] = $slug;

        $room = Room::create($validated);

        $this->handleGalleryUploads($request, $room);

        return redirect()->route('admin.rooms.index')->with('success', 'Room created successfully.');
    }

    public function edit(Room $room)
    {
        return view('admin.rooms.form', compact('room'));
    }

    public function update(Request $request, Room $room)
    {
        $validated = $this->validate($request);

        if ($request->hasFile('cover_image')) {
            app(ImagePreview::class)->delete($room->cover_image);
            if ($room->cover_image) Storage::disk('public')->delete($room->cover_image);
            $validated['cover_image'] = $request->file('cover_image')->store('rooms', 'public');
            app(ImagePreview::class)->generate($validated['cover_image']);
        }

        $validated['amenities'] = $this->parseAmenities($request->input('amenities_raw', ''));

        $room->update($validated);

        $this->handleGalleryUploads($request, $room);

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated successfully.');
    }

    public function toggleStatus(Room $room)
    {
        $room->update(['is_active' => ! $room->is_active]);
        return back()->with('success', 'Room status updated.');
    }

    public function destroy(Room $room)
    {
        app(ImagePreview::class)->delete($room->cover_image);
        if ($room->cover_image) Storage::disk('public')->delete($room->cover_image);
        $room->images()->each(fn($img) => Storage::disk('public')->delete($img->image_path));
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted.');
    }

    public function destroyImage(RoomImage $image)
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
        return back()->with('success', 'Image removed.');
    }

    private function validate(Request $request): array
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'category'          => 'required|in:deluxe,premium,suite',
            'tagline'           => 'nullable|string|max:255',
            'price_per_night'   => 'required|numeric|min:0',
            'size_sqm'          => 'nullable|integer|min:1',
            'max_guests'        => 'required|integer|min:1',
            'bed_type'          => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:500',
            'description'       => 'nullable|string',
            'cover_image'       => 'nullable|image|max:6096',
            'gallery_images'    => 'nullable|array|max:20',
            'gallery_images.*'  => 'required|image|mimes:jpg,jpeg,png,webp|max:6096',
            'amenities_raw'     => 'nullable|string',
            'is_active'         => 'boolean',
            'sort_order'        => 'integer|min:0',
        ]);

        unset($validated['gallery_images'], $validated['amenities_raw']);

        return $validated;
    }

    private function parseAmenities(?string $raw): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", $raw ?? '')), fn ($value) => $value !== ''));
    }

    private function handleGalleryUploads(Request $request, Room $room): void
    {
        if ($request->hasFile('gallery_images')) {
            $lastOrder = $room->images()->max('sort_order') ?? 0;
            foreach ($request->file('gallery_images') as $i => $file) {
                $path = $file->store('rooms/gallery', 'public');
                $room->images()->create(['image_path' => $path, 'sort_order' => $lastOrder + $i + 1]);
            }
        }
    }
}
