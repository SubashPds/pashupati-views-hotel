<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\SiteSetting;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('frontend.faq', [
            'faqs'     => Faq::active()->get(),
            'settings' => SiteSetting::pluck('value', 'key'),
        ]);
    }
}
