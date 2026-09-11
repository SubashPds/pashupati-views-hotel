<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $data['media_path'] = $file->store('hero-slides', 'public');
        $data['media_type'] = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
        HeroSlide::create($data);
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
        $oldPath = $heroSlide->media_path;
        if ($file = $request->file('media')) {
            $data['media_path'] = $file->store('hero-slides', 'public');
            $data['media_type'] = str_starts_with($file->getMimeType(), 'video/') ? 'video' : 'image';
        }
        $heroSlide->update($data);
        if (isset($data['media_path'])) {
            Storage::disk('public')->delete($oldPath);
        }
        return redirect()->route('admin.hero-slides.index')->with('success', 'Slide updated.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $path = $heroSlide->media_path;
        $heroSlide->delete();
        Storage::disk('public')->delete($path);
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
