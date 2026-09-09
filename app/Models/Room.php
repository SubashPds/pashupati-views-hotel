<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Room extends Model
{
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
}
