<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getFormattedPriceAttribute(): string
    {
        $label = trim($this->price_label ?? '');

        if ($label === '') {
            return $this->price_from !== null
                ? 'From NPR ' . number_format($this->price_from, 0)
                : 'Contact for pricing';
        }

        // Preserve explicit currencies and non-numeric labels such as "Price on request".
        if (preg_match('/\p{Sc}|\b(?:NPR|USD|EUR|GBP|INR|AUD|CAD|Rs)\b|रु|रू/iu', $label)) {
            return $label;
        }

        return preg_replace('/\p{N}/u', 'NPR $0', $label, 1);
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
