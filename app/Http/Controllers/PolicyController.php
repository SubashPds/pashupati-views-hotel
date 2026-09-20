<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use Illuminate\View\View;

class PolicyController extends Controller
{
    public function privacyPolicy(): View
    {
        $settings = \App\Models\SiteSetting::orderBy('sort_order')->pluck('value', 'key');
        $policy = Policy::forCategory(Policy::PRIVACY_POLICY)->first();

        return view('frontend.policies.privacy', compact('policy', 'settings'));
    }

    public function termsAndConditions(): View
    {
        $settings = \App\Models\SiteSetting::orderBy('sort_order')->pluck('value', 'key');
        $policy = Policy::forCategory(Policy::TERMS_AND_CONDITIONS)->first();

        return view('frontend.policies.terms', compact('policy', 'settings'));
    }
}
