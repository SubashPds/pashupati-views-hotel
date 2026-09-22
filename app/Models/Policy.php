<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;

class Policy extends Model
{
    use TracksUserChanges;

    public const PRIVACY_POLICY = 'privacy_policy';
    public const TERMS_AND_CONDITIONS = 'terms_and_conditions';

    public const CATEGORIES = [
        self::PRIVACY_POLICY => 'Privacy Policy',
        self::TERMS_AND_CONDITIONS => 'Terms & Conditions',
    ];

    protected $fillable = ['category', 'title', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function fixedCategories(): array
    {
        return array_keys(self::CATEGORIES);
    }

    public static function fixedCategoryLabels(): array
    {
        return self::CATEGORIES;
    }

    public function safeDescriptionHtml(): string
    {
        return \App\Support\SafeHtml::clean($this->description);
    }
}
