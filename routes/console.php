<?php

use App\Services\GalleryPreview;
use App\Services\ImagePreview;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('gallery:previews {--force : Regenerate existing previews}', function (GalleryPreview $previews) {
    $ready = 0;
    $skipped = 0;
    foreach (Storage::disk('public')->files('gallery') as $source) {
        if (! $previews->path($source)) {
            continue;
        }
        $previews->generate($source, (bool) $this->option('force')) ? $ready++ : $skipped++;
    }
    $this->info("Gallery previews ready: {$ready}; skipped: {$skipped}.");
    if ($skipped) {
        $this->warn('Skipped images keep their original URLs. Preview generation needs GD with WebP or ImageMagick (magick/convert).');
    }
})->purpose('Create small WebP previews for local gallery uploads without changing originals or querying the database');

Artisan::command('cards:previews {--force : Regenerate existing previews}', function (ImagePreview $previews) {
    $ready = 0;
    $skipped = 0;
    foreach (['rooms', 'packages'] as $directory) {
        foreach (Storage::disk('public')->files($directory) as $source) {
            if (! $previews->path($source)) {
                continue;
            }
            $previews->generate($source, (bool) $this->option('force')) ? $ready++ : $skipped++;
        }
    }
    $this->info("Room/package cover previews ready: {$ready}; skipped: {$skipped}.");
    if ($skipped) {
        $this->warn('Skipped images keep their original URLs. Preview generation needs GD with WebP or ImageMagick (magick/convert).');
    }
})->purpose('Create small WebP previews for room/package covers without processing their detail galleries or querying the database');
