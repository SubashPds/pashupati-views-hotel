<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class VisitorCountry
{
    public function detect(Request $request): ?string
    {
        // Accept an edge-provided country only when the edge is an explicitly trusted proxy.
        if ($request->isFromTrustedProxy() && ($header = config('currency.country_header'))) {
            $country = $this->country($request->header($header));
            if ($country) return $country;
        }
        $ip = $request->ip();
        if (!filter_var($ip, FILTER_VALIDATE_IP)) return null;
        // Local previews have no publicly geolocatable visitor IP; use the hotel's base currency.
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) return 'NP';
        if (!config('currency.geoip_enabled')) return null;

        $cache = Cache::store(config('currency.cache_store'));
        $key = 'visitor-country:v1:'.hash('sha256', $ip);
        $cached = $cache->get($key);
        if (is_string($cached)) return $cached ?: null;
        try {
            $country = $this->country(Http::connectTimeout(1)->timeout(2)
                ->get('https://get.geojs.io/v1/ip/country/'.rawurlencode($ip).'.json')->throw()->json('country'));
            $cache->put($key, $country ?? '', $country ? 86400 : 300);
            return $country;
        } catch (Throwable) {
            $cache->put($key, '', 300);
            return null;
        }
    }

    private function country(mixed $value): ?string
    {
        if (!is_string($value)) return null;
        $code = strtoupper(trim($value));
        return preg_match('/^[A-Z]{2}$/', $code) && !in_array($code, ['XX', 'ZZ'], true) ? $code : null;
    }
}
