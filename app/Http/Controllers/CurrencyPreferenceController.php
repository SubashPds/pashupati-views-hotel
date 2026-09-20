<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Room;
use App\Models\Service;
use App\Services\DisplayCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CurrencyPreferenceController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'country' => ['required_without:currency', Rule::in(['NP', 'IN', 'OTHER'])],
            'currency' => ['required_without:country', Rule::in(['NPR', 'INR', 'USD'])],
            'return_to' => ['nullable', 'string', 'regex:~^/(?:blogs(?:/[a-zA-Z0-9_-]+)?)?$~'],
        ]);
        $code = $data['currency'] ?? match ($data['country']) {
            'NP' => 'NPR', 'IN' => 'INR', default => 'USD',
        };

        if ($request->expectsJson()) {
            $currency = DisplayCurrency::fromCode($code);
            $prices = [];
            foreach (Room::active()->get() as $room) {
                $prices['room-'.$room->id] = $currency->format($room->price_per_night);
                $prices['room-amount-'.$room->id] = $currency->amount($room->price_per_night);
            }
            foreach (Package::active()->get() as $package) {
                $prices['package-'.$package->id] = $currency->package($package);
            }
            foreach (Service::active()->get() as $service) {
                $prices['service-'.$service->id] = $currency->label($service->price_label);
            }
            $response = response()->json(['currency' => $code, 'prices' => $prices, 'message' => 'Prices updated to '.$code.'.']);
        } else {
            $response = redirect($data['return_to'] ?? '/');
        }

        return $response
            ->withCookie(cookie('display_currency', $code, 60 * 24 * 365, '/', null, $request->isSecure(), true, false, 'lax'))
            ->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
