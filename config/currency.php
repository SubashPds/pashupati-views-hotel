<?php

return [
    'geoip_enabled' => env('CURRENCY_GEOIP_ENABLED', true),
    'cache_store' => env('CURRENCY_CACHE_STORE', 'file'),
    'country_header' => env('CURRENCY_COUNTRY_HEADER'),
];
