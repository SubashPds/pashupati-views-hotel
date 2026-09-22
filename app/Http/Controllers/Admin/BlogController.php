<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Services\MediaFiles;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

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
        MediaFiles::transaction(function (MediaFiles $files) use ($request, $data) {
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $files->store($request->file('cover_image'), 'blogs');
            }
            if (! (new Blog($data))->save()) {
                throw new RuntimeException('The blog could not be saved.');
            }
        });

        return redirect()->route('admin.blogs.index')->with('success', 'Blog created.');
    }

    public function edit(Blog $blog)
    {
        return view('admin.blogs.form', compact('blog'));
    }

    public function update(Request $request, Blog $blog)
    {
        $data = $this->validateBlog($request);
        MediaFiles::transaction(function (MediaFiles $files) use ($request, $blog, $data) {
            $blog = Blog::lockForUpdate()->findOrFail($blog->id);
            if ($request->hasFile('cover_image')) {
                $data['cover_image'] = $files->store($request->file('cover_image'), 'blogs');
            } elseif ($request->boolean('remove_cover')) {
                $data['cover_image'] = null;
            }
            if (array_key_exists('cover_image', $data)) {
                $files->deleteAfterCommit($blog->cover_image);
            }
            if ($data['is_published'] && ! $blog->published_at) {
                $data['published_at'] = now();
            }
            if (! $blog->update($data)) {
                throw new RuntimeException('The blog could not be saved.');
            }
        });

        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated.');
    }

    public function destroy(Blog $blog)
    {
        MediaFiles::transaction(function (MediaFiles $files) use ($blog) {
            $blog = Blog::lockForUpdate()->findOrFail($blog->id);
            $files->deleteAfterCommit($blog->cover_image);
            if (! $blog->delete()) {
                throw new RuntimeException('The blog could not be deleted.');
            }
        });

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
