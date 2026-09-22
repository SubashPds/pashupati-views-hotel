<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Services\MediaFiles;
use Illuminate\Http\Request;
use RuntimeException;

class HeroSlideController extends Controller
{
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.hero-slides.index', compact('slides'));
    }

    public function create()
    {
        return view('admin.hero-slides.form', ['slide' => new HeroSlide]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, true);
        $file = $request->file('media');
        unset($data['media']);
        $data['media_type'] = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
        MediaFiles::transaction(function (MediaFiles $files) use ($file, $data) {
            $data['media_path'] = $files->store($file, 'hero-slides');
            if (! (new HeroSlide($data))->save()) {
                throw new RuntimeException('The slide could not be saved.');
            }
        });
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide created.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.form', ['slide' => $heroSlide]);
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $data = $this->validated($request, false);
        unset($data['media']);
        MediaFiles::transaction(function (MediaFiles $files) use ($request, $heroSlide, $data) {
            $heroSlide = HeroSlide::lockForUpdate()->findOrFail($heroSlide->id);
            if ($file = $request->file('media')) {
                $data['media_path'] = $files->store($file, 'hero-slides');
                $data['media_type'] = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
                $files->deleteAfterCommit($heroSlide->media_path);
            }
            if (! $heroSlide->update($data)) {
                throw new RuntimeException('The slide could not be saved.');
            }
        });
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide updated.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($heroSlide) {
            $heroSlide = HeroSlide::lockForUpdate()->findOrFail($heroSlide->id);
            $files->deleteAfterCommit($heroSlide->media_path);
            if (! $heroSlide->delete()) {
                throw new RuntimeException('The slide could not be deleted.');
            }
        });
        return back()->with('success', 'Slide deleted.');
    }

    private function validated(Request $request, bool $creating): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'media' => [$creating ? 'required' : 'nullable', 'file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm', 'max:51200'],
            'sort_order' => 'required|integer|min:0|max:100000',
            'is_active' => 'required|boolean',
        ]);
    }
}
