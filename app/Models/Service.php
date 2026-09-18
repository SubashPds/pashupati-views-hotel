<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use TracksUserChanges;

    protected $fillable = ['title', 'description', 'icon', 'price_label', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
