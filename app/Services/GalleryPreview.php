<?php

namespace App\Services;

class GalleryPreview extends ImagePreview
{
    public function path(?string $source): ?string
    {
        return str_starts_with($source ?? '', 'gallery/') ? parent::path($source) : null;
    }
}
