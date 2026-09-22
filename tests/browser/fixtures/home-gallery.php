<?php

// Render the real homepage section without a database or modifying CMS content.
require getcwd().'/vendor/autoload.php';
$app = require getcwd().'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$items = collect(range(1, 9))->map(function ($id) {
    $item = new App\Models\GalleryItem([
        'title' => 'Gallery item '.$id,
        'image_path' => $id === 2 ? '/qa/video.mp4' : '/qa/photo-'.$id.'.jpg',
        'section' => 'hotel',
    ]);
    $item->id = $id;

    return $item;
});

echo '<!doctype html><html><head><link rel="stylesheet" href="/qa/app.css"></head><body><div style="height:5000px">Homepage content before gallery</div>';
echo view('frontend.sections.gallery', ['galleryItems' => $items, 'settings' => [], 'limit' => 8])->render();
echo '<div style="height:1000px">Content after gallery</div><script type="module" src="/resources/js/gallery-viewer.js"></script><script type="module" src="/resources/js/home-gallery.js"></script></body></html>';
