<?php

// Render the real booking markup and inline handler without a database or server.
require dirname(__DIR__, 3).'/vendor/autoload.php';
$temporary = sys_get_temp_dir().'/pashupati-booking-dialog-test';
if (! is_dir($temporary.'/views')) {
    mkdir($temporary.'/views', 0777, true);
}
putenv('APP_CONFIG_CACHE='.$temporary.'/config.php');
$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->useEnvironmentPath($temporary);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver' => 'array', 'view.compiled' => $temporary.'/views']);
Illuminate\Support\Facades\URL::forceRootUrl('http://booking.test');
session()->start();

$layout = file_get_contents(resource_path('views/layouts/app.blade.php'));
$start = strpos($layout, '<dialog id="booking-dialog"');
$end = $start === false ? false : strpos($layout, "@include('frontend.sections.offer')", $start);
if ($start === false || $end === false) {
    throw new RuntimeException('The booking dialog, inline handler, and success toast were not found.');
}
$fragment = substr($layout, $start, $end - $start);

echo '<!doctype html><html><head><meta name="viewport" content="width=device-width, initial-scale=1"><link rel="stylesheet" href="/app.css"></head><body>';
echo '<button type="button" data-open-booking>Open booking</button>';
echo Illuminate\Support\Facades\Blade::render($fragment, ['rooms' => collect()]);
echo '<script type="module" src="/resources/js/secure-actions.js"></script><script type="module" src="/resources/js/frontend-forms.js"></script></body></html>';
