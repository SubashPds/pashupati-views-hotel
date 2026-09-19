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
use App\Services\EnquiryEmailNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

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
        $packages = Package::with('images')->active()->orderBy('sort_order')->get();

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
     * Dedicated contact page.
     */
    public function contact(): View
    {
        $settings = SiteSetting::orderBy('sort_order')->pluck('value', 'key');
        return view('frontend.contact', compact('settings'));
    }

    /**
     * Handle enquiry / contact form submission.
     */
    public function enquire(Request $request, EnquiryEmailNotifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'email'      => 'nullable|email|max:255',
            'phone'      => 'nullable|string|max:30',
            'category'   => 'nullable|string|max:100',
            'message'    => 'nullable|string|max:3000',
            'checkin'    => 'nullable|date_format:Y-m-d',
            'checkout'   => array_filter(['nullable', 'date_format:Y-m-d', $request->filled('checkin') ? 'after_or_equal:checkin' : null]),
            'guests'     => ['nullable', Rule::in(['1', '2', '3', '4', '5+'])],
        ]);

        if (str_starts_with($validated['category'] ?? '', 'package:')) {
            $packageId = substr($validated['category'], strlen('package:'));
            $package = ctype_digit($packageId) ? Package::active()->find($packageId) : null;
            if (!$package) {
                return back()->withInput()->withErrors(['category' => 'This package is no longer available. Please choose another enquiry type.']);
            }
            $validated['category'] = 'Packages';
            $validated['message'] = trim('Package: '.$package->name."\n\n".($validated['message'] ?? ''));
        }

        if (empty($validated['email']) && empty($validated['phone'])) {
            return back()
                ->withInput()
                ->withErrors(['contact' => 'Please provide at least an email address or phone number.']);
        }

        $stayDetails = [];
        foreach (['checkin' => 'Check-in', 'checkout' => 'Check-out', 'guests' => 'Guests'] as $key => $label) {
            if (!empty($validated[$key])) $stayDetails[] = $label.': '.$validated[$key];
            unset($validated[$key]);
        }
        if ($stayDetails) $validated['message'] = trim(($validated['message'] ?? '')."\n\n".implode("\n", $stayDetails));

        $enquiry = Enquiry::create($validated);
        $notifier->send($enquiry);

        return back()->with('enquiry_success', true);
    }
}
