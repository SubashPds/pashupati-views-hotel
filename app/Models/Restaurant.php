<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Restaurant extends Model
{
    use TracksUserChanges;

    protected $fillable = [
        'name', 'description', 'overview', 'opening_time', 'closing_time',
        'breakfast_start_time', 'breakfast_end_time', 'lunch_start_time', 'lunch_end_time',
        'dinner_start_time', 'dinner_end_time', 'cuisines', 'featured_dishes', 'menu_image', 'is_active',
    ];

    protected $casts = ['cuisines' => 'array', 'featured_dishes' => 'array', 'is_active' => 'boolean'];

    public function images(): HasMany
    {
        return $this->hasMany(RestaurantImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
