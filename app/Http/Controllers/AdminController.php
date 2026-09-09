<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Room;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'rooms'        => Room::count(),
            'enquiries'    => Enquiry::where('status', 'new')->count(),
            'testimonials' => Testimonial::count(),
        ];

        $recentEnquiries = Enquiry::latest()->take(5)->get();

        return view('admin.dashboard', compact('stats', 'recentEnquiries'));
    }
}
