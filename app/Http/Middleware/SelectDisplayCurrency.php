<?php

namespace App\Http\Middleware;

use App\Services\DisplayCurrency;
use App\Services\VisitorCountry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SelectDisplayCurrency
{
    public function handle(Request $request, Closure $next): Response
    {
        $code = $request->cookie('display_currency');
        $hasPreference = in_array($code, ['NPR', 'INR', 'USD'], true);
        if (!$hasPreference) {
            $country = app(VisitorCountry::class)->detect($request);
            $code = match ($country) { 'NP' => 'NPR', 'IN' => 'INR', default => 'USD' };
        }
        view()->share('showCountryPrompt', !$hasPreference);
        view()->share('currency', DisplayCurrency::fromCode($code));
        $response = $next($request);
        // Prevent a page cached for one country being shown to visitors from another.
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
