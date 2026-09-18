<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Room extends Model
{
    use TracksUserChanges;

    protected $fillable = [
        'name', 'slug', 'category', 'tagline', 'price_per_night',
        'size_sqm', 'max_guests', 'bed_type', 'short_description',
        'description', 'amenities', 'cover_image', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'amenities'       => 'array',
        'is_active'       => 'boolean',
        'price_per_night' => 'decimal:2',
    ];

    protected $appends = ['cover_image_url'];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Room $room) {
            if (empty($room->slug)) {
                $room->slug = Str::slug($room->name);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getFormattedPriceAttribute(): string
    {
        return 'NPR ' . number_format($this->price_per_night, 0) . ' / night';
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
