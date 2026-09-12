<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class Promotion extends Model
{
    public const TIMEZONE = 'Asia/Kathmandu';

    protected $fillable = ['title', 'label', 'description', 'image_path', 'image_alt', 'button_text', 'button_url', 'is_active', 'sort_order', 'end_date'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer', 'end_date' => 'date:Y-m-d'];
    }

    public function scopeVisible(Builder $query): Builder
    {
        // End dates include the entire selected day in the hotel's local time.
        return $query->where('is_active', true)->where(function (Builder $query) {
            $query->whereNull('end_date')->orWhere('end_date', '>=', now(self::TIMEZONE)->toDateString());
        });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->toDateString() < now(self::TIMEZONE)->toDateString();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}
