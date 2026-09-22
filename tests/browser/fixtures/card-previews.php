<?php

// Real Blade views with isolated media URLs; no database or CMS writes.
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rooms = collect(range(1, 3))->map(function ($id) {
    $room = new class extends App\Models\Room
    {
        public function getCoverPreviewUrlAttribute(): string
        {
            return '/qa/previews/room-'.$this->id.'.webp';
        }
    };
    $room->fill(['name' => 'Room '.$id, 'category' => $id === 2 ? 'premium' : 'deluxe', 'price_per_night' => 1600, 'cover_image' => '/qa/originals/room-'.$id.'.jpg']);
    $room->id = $id;
    $room->setRelation('images', collect());

    return $room;
});
$packages = collect(range(1, 3))->map(function ($id) {
    $package = new class extends App\Models\Package
    {
        public function getCoverPreviewUrlAttribute(): string
        {
            return '/qa/previews/package-'.$this->id.'.webp';
        }
    };
    $package->fill(['name' => 'Package '.$id, 'price_from' => 3200, 'cover_image' => '/qa/originals/package-'.$id.'.jpg']);
    $package->id = $id;
    $package->setRelation('images', collect());

    return $package;
});
$currency = new App\Services\DisplayCurrency('NPR', ['NPR' => 1, 'INR' => 1.6, 'USD' => 160]);
$data = ['rooms' => $rooms, 'packages' => $packages, 'settings' => [], 'currency' => $currency];
$html = '<!doctype html><html><head><link rel="stylesheet" href="/qa/app.css"></head><body><div style="height:5000px">Content before cards</div>';
$html .= Illuminate\Support\Facades\Blade::render("@include('frontend.sections.rooms') @include('frontend.sections.packages') @include('frontend.partials.detail-photo-viewer') @stack('scripts')", $data);
$html .= '<div style="height:1000px">Content after cards</div><script type="module" src="/resources/js/card-previews.js"></script><script type="module" src="/resources/js/room-details.js"></script><script type="module" src="/resources/js/package-details.js"></script></body></html>';
echo json_encode([
    'html' => $html,
    'roomDetails' => view('frontend.partials.room-details', ['room' => $rooms->first(), 'currency' => $currency])->render(),
    'packageDetails' => view('frontend.partials.package-details', ['pkg' => $packages->first(), 'currency' => $currency])->render(),
]);
