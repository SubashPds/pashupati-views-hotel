<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = ['title', 'image_path', 'badge_label', 'section', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    protected $appends = ['image_url'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /**
     * Resolve public URL regardless of whether the path is:
     *  - a full https:// URL
     *  - a path starting with / (served from public/)
     *  - a relative path stored in storage/app/public
     */
    public function getImageUrlAttribute(): string
    {
        $path = $this->image_path;

        if (empty($path)) {
            return '';
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        return Storage::url($path);
    }
}
