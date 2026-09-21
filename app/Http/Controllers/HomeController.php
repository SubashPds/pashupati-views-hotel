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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        // Room cards only; full descriptions and galleries load on demand.
        $rooms = Room::active()
            ->select(['id', 'name', 'category', 'tagline', 'price_per_night', 'size_sqm', 'max_guests', 'bed_type', 'short_description', 'amenities', 'cover_image'])
            ->orderBy('sort_order')
            ->get();

        // Experience highlights
        $experiences = Experience::active()->orderBy('sort_order')->get();

        // Gallery (active only, ordered, paginated per page)
        $galleryItems = GalleryItem::active()->orderBy('sort_order')->take(10)->get();

        // Services
        $services = Service::active()->orderBy('sort_order')->get();

        // Testimonials
        $testimonials = Testimonial::active()->orderBy('sort_order')->get();

        // Package cards only; descriptions, highlights, and galleries load on demand.
        $packages = Package::active()
            ->select(['id', 'name', 'tagline', 'badge', 'short_description', 'price_label', 'price_from', 'duration', 'min_guests', 'max_guests', 'includes', 'cover_image'])
            ->get();

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

    public function roomDetails(Room $room): View
    {
        abort_unless($room->is_active, 404);
        $room->load('images');

        return view('frontend.partials.room-details', compact('room'));
    }

    public function packageDetails(Package $package): View
    {
        abort_unless($package->is_active, 404);
        $package->load('images');

        return view('frontend.partials.package-details', ['pkg' => $package]);
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
     * Dedicated gallery page.
     */
    public function gallery(Request $request): View
    {
        $settings = SiteSetting::orderBy('sort_order')->pluck('value', 'key');
        $categoryExpression = "COALESCE(NULLIF(LOWER(TRIM(section)), ''), 'general')";
        $categoryCounts = GalleryItem::active()->reorder()
            ->selectRaw($categoryExpression.' AS category, COUNT(*) AS total')
            ->groupByRaw($categoryExpression)->orderBy('category')->pluck('total', 'category');
        $category = $request->query('category', '');
        abort_unless(is_string($category), 404);
        $category = strtolower(trim($category));
        abort_unless($category === '' || $categoryCounts->has($category), 404);

        $galleryItems = GalleryItem::active()
            ->when($category !== '', fn ($query) => $query->whereRaw($categoryExpression.' = ?', [$category]))
            ->orderBy('id')->paginate(12)
            ->appends($category !== '' ? ['category' => $category] : [])->fragment('gallery');

        return view('frontend.gallery', compact('settings', 'galleryItems', 'categoryCounts', 'category'));
    }

    /**
     * Handle enquiry / contact form submission.
     */
    public function enquire(Request $request, EnquiryEmailNotifier $notifier): RedirectResponse|JsonResponse
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
                throw ValidationException::withMessages(['category' => 'This package is no longer available. Please choose another enquiry type.']);
            }
            $validated['category'] = 'Packages';
            $validated['message'] = trim('Package: '.$package->name."\n\n".($validated['message'] ?? ''));
        }

        if (empty($validated['email']) && empty($validated['phone'])) {
            throw ValidationException::withMessages(['contact' => 'Please provide at least an email address or phone number.']);
        }

        $stayDetails = [];
        foreach (['checkin' => 'Check-in', 'checkout' => 'Check-out', 'guests' => 'Guests'] as $key => $label) {
            if (!empty($validated[$key])) $stayDetails[] = $label.': '.$validated[$key];
            unset($validated[$key]);
        }
        if ($stayDetails) $validated['message'] = trim(($validated['message'] ?? '')."\n\n".implode("\n", $stayDetails));

        $enquiry = Enquiry::create($validated);
        $notifier->send($enquiry);

        if ($request->expectsJson()) {
            return response()->json(['message' => "Thank you! We've received your enquiry and will respond within 24 hours."], 201);
        }

        return back()->with('enquiry_success', true);
    }
}
