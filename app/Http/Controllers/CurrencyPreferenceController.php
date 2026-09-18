<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyPreferenceController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'country' => ['required_without:currency', Rule::in(['NP', 'IN', 'OTHER'])],
            'currency' => ['required_without:country', Rule::in(['NPR', 'INR', 'USD'])],
            'return_to' => ['nullable', 'string', 'regex:~^/(?:blogs(?:/[a-zA-Z0-9_-]+)?)?$~'],
        ]);
        $code = $data['currency'] ?? match ($data['country']) {
            'NP' => 'NPR', 'IN' => 'INR', default => 'USD',
        };

        return redirect($data['return_to'] ?? '/')
            ->withCookie(cookie('display_currency', $code, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'))
            ->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
