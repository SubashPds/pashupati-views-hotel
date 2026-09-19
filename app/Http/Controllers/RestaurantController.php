<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\SiteSetting;

class RestaurantController extends Controller
{
    public function index()
    {
        $restaurant = Restaurant::with('images')->whereKey(1)->where('is_active', true)->first();
        $settings = SiteSetting::pluck('value', 'key');

        return view('frontend.restaurant', compact('restaurant', 'settings'));
    }
}
