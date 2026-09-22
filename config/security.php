<?php

return [
    // Exact hostnames only. Set TRUSTED_HOSTS for an additional www or staging hostname.
    'trusted_hosts' => array_values(array_filter(array_map('trim', explode(',', env('TRUSTED_HOSTS', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'))))),
    'bootstrap_admin_email' => env('ADMIN_EMAIL', 'superadmin@pashupativiews.com'),
    'bootstrap_admin_password' => env('ADMIN_PASSWORD'),
];
