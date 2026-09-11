<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    protected $fillable = ['title', 'media_path', 'media_type', 'sort_order', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'sort_order' => 'integer'];

    public function getMediaUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->media_path);
    }
}
