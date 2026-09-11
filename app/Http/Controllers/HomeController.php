<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Experience;
use App\Models\GalleryItem;
use App\Models\Package;
use App\Models\Room;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Public homepage – all CMS data eagerly loaded in one place.
     */
    public function index(): View
    {
        $heroSlides = \App\Models\HeroSlide::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get();

        // Load settings as flat array
        $settings = SiteSetting::orderBy('sort_order')
            ->pluck('value', 'key');

        // Active rooms, eager-load images, ordered by sort
        $rooms = Room::with('images')
            ->active()
            ->orderBy('sort_order')
            ->get();

        // Experience highlights
        $experiences = Experience::active()->orderBy('sort_order')->get();

        // Gallery (active only, ordered, paginated per page)
        $galleryItems = GalleryItem::active()->orderBy('sort_order')->get();

        // Services
        $services = Service::active()->orderBy('sort_order')->get();

        // Testimonials
        $testimonials = Testimonial::active()->orderBy('sort_order')->get();

        // Packages
        $packages = Package::active()->orderBy('sort_order')->get();

        return view('frontend.home', compact(
            'heroSlides',
            'settings',
            'rooms',
            'experiences',
            'galleryItems',
            'services',
            'testimonials',
            'packages',
        ));
    }

    /**
     * Handle enquiry / contact form submission.
     */
    public function enquire(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:30',
            'category'   => 'nullable|string|max:100',
            'message'    => 'nullable|string|max:3000',
        ]);

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()
                ->withInput()
                ->withErrors(['contact' => 'Please provide at least an email address or phone number.']);
        }

        Enquiry::create($validated);

        return back()->with('enquiry_success', true);
    }
}
