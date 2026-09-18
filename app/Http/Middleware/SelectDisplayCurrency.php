<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use App\Services\DisplayCurrency;
use App\Services\VisitorCountry;
use App\Support\CurrencySettings;
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
        $defaults = CurrencySettings::defaults();
        $settings = SiteSetting::whereIn('key', array_keys($defaults))->pluck('value', 'key')->all();
        $rates = ['NPR' => 1.0];
        foreach (['INR' => 'currency_npr_per_inr', 'USD' => 'currency_npr_per_usd'] as $currency => $key) {
            $value = $settings[$key] ?? $defaults[$key];
            $rates[$currency] = is_numeric($value) && (float) $value > 0 ? (float) $value : (float) $defaults[$key];
        }
        view()->share('currency', new DisplayCurrency($code, $rates));
        $response = $next($request);
        // Prevent a page cached for one country being shown to visitors from another.
        $response->headers->set('Cache-Control', 'private, no-store');
        return $response;
    }
}
