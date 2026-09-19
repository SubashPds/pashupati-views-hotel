<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.blogs.index', ['blogs' => Blog::latest()->latest('id')->paginate(15)]);
    }

    public function create()
    {
        return view('admin.blogs.form', ['blog' => new Blog()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBlog($request);
        $base = Str::slug(Str::limit($data['title'], 180, '')) ?: 'article';
        $slug = $base;
        for ($suffix = 2; Blog::where('slug', $slug)->exists(); $suffix++) {
            $slug = $base.'-'.$suffix;
        }
        $data['slug'] = $slug;
        $data['published_at'] = $data['is_published'] ? now() : null;
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blogs', 'public');
        }
        Blog::create($data);

        return redirect()->route('admin.blogs.index')->with('success', 'Blog created.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.form', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validateBlog($request);
        $oldCover = $blog->cover_image;
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blogs', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $data['cover_image'] = null;
        }
        if ($data['is_published'] && ! $blog->published_at) {
            $data['published_at'] = now();
        }
        $blog->update($data);
        if ($oldCover && $oldCover !== $blog->cover_image) {
            Storage::disk('public')->delete($oldCover);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated.');
    }

    public function destroy(Blog $blog)
    {
        $cover = $blog->cover_image;
        $blog->delete();
        if ($cover) {
            Storage::disk('public')->delete($cover);
        }

        return redirect()->route('admin.blogs.index')->with('success', 'Blog deleted.');
    }

    private function validateBlog(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|max:100000',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:6096',
            'is_published' => 'required|boolean',
            'remove_cover' => 'nullable|boolean',
        ]);
        unset($data['cover_image'], $data['remove_cover']);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
