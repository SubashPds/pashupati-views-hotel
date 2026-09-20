<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Room;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $stats = [
            'rooms'        => $user->hasPermission('rooms') ? Room::count() : null,
            'enquiries'    => $user->hasPermission('enquiries') ? Enquiry::where('status', 'new')->count() : null,
            'testimonials' => $user->hasPermission('testimonials') ? Testimonial::count() : null,
        ];

        $recentEnquiries = $user->hasPermission('enquiries') ? Enquiry::latest()->latest('id')->take(5)->get() : collect();

        return view('admin.dashboard', compact('stats', 'recentEnquiries'));
    }
}
