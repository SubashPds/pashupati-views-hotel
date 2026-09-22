<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Services\MediaFiles;
use Illuminate\Http\Request;
use RuntimeException;

class GalleryController extends Controller
{
    public function index()
    {
        $items = GalleryItem::orderBy('id', 'desc')->paginate(10);
        return view('admin.gallery.index', compact('items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|file|mimetypes:image/jpeg,image/png,image/webp,image/gif,image/bmp,image/avif,video/mp4,video/webm|max:51200',
            'title'    => 'nullable|string|max:255',
            'badge_label' => 'nullable|string|max:100',
            'section'  => 'nullable|string|max:100',
        ]);

        MediaFiles::transaction(function (MediaFiles $files) use ($request) {
            $lastOrder = GalleryItem::max('sort_order') ?? 0;
            foreach ($request->file('images', []) as $i => $file) {
                $item = new GalleryItem([
                    'image_path'  => $files->store($file, 'gallery', preview: true),
                    'title'       => $request->title,
                    'badge_label' => $request->badge_label,
                    'section'     => $request->section ?? 'general',
                    'is_active'   => true,
                    'sort_order'  => $lastOrder + $i + 1,
                ]);
                if (! $item->save()) {
                    throw new RuntimeException('The gallery item could not be saved.');
                }
            }
        });

        return back()->with('success', 'Gallery media uploaded successfully.');
    }

    public function edit(GalleryItem $galleryItem)
    {
        return view('admin.gallery.form', compact('galleryItem'));
    }

    public function update(Request $request, GalleryItem $galleryItem)
    {
        $validated = $request->validate([
            'sort_order' => 'sometimes|required|integer|min:0',
            'title'       => 'nullable|string|max:255',
            'badge_label' => 'nullable|string|max:100',
            'section'     => 'nullable|string|max:100',
        ]);

        if (array_key_exists('section', $validated)) {
            $validated['section'] ??= 'general';
        }
        $galleryItem->update($validated);
        return redirect()->route('admin.gallery.index')->with('success', 'Gallery item updated.');
    }

    public function toggleStatus(GalleryItem $galleryItem)
    {
        $galleryItem->update(['is_active' => ! $galleryItem->is_active]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(GalleryItem $galleryItem)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($galleryItem) {
            $files->deleteAfterCommit($galleryItem->image_path);
            if (! $galleryItem->delete()) {
                throw new RuntimeException('The gallery item could not be deleted.');
            }
        });
        return back()->with('success', 'Gallery item deleted.');
    }
}
