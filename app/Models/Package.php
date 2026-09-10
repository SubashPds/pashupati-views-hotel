<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = [
        'name', 'slug', 'tagline', 'badge',
        'short_description', 'description',
        'price_label', 'price_from',
        'duration', 'min_guests', 'max_guests',
        'includes', 'highlights',
        'cover_image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'includes'   => 'array',
        'highlights' => 'array',
        'is_active'  => 'boolean',
        'price_from' => 'decimal:2',
    ];

    protected $appends = ['cover_image_url'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Package $pkg) {
            if (empty($pkg->slug)) {
                $pkg->slug = Str::slug($pkg->name);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    public function getCoverImageUrlAttribute(): string
    {
        if (empty($this->cover_image)) {
            return '';
        }
        if (str_starts_with($this->cover_image, 'http') || str_starts_with($this->cover_image, '/')) {
            return asset(ltrim($this->cover_image, '/'));
        }
        return Storage::url($this->cover_image);
    }
}
