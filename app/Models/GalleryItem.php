<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use App\Services\GalleryPreview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    use TracksUserChanges;

    protected $fillable = ['title', 'image_path', 'badge_label', 'section', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    protected $appends = ['image_url'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getMediaTypeAttribute(): string
    {
        $path = parse_url($this->image_path ?? '', PHP_URL_PATH) ?: '';

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'webm'], true)
            ? 'video'
            : 'image';
    }

    public function getPreviewUrlAttribute(): string
    {
        return app(GalleryPreview::class)->url($this->image_path) ?? $this->image_url;
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
