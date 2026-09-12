<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\SiteSetting;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        return view('frontend.blogs.index', [
            'blogs' => Blog::published()->latest('published_at')->latest('id')->paginate(9),
            'settings' => SiteSetting::pluck('value', 'key'),
        ]);
    }

    public function show(string $slug): View
    {
        return view('frontend.blogs.show', [
            'blog' => Blog::published()->where('slug', $slug)->firstOrFail(),
            'settings' => SiteSetting::pluck('value', 'key'),
        ]);
    }
}
