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
        $html = (string) ($this->description ?? '');

        if ($html === '') {
            return '';
        }

        $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><blockquote><span><div><table><thead><tbody><tr><td><th><img><figure><figcaption><small><sub><sup><code><pre><hr><strong><b><strike><mark>';

        $html = preg_replace('/<\s*(script|iframe|object|embed|style|svg|math|form|input|button|textarea|select|option)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $html) ?? $html;
        $html = preg_replace('/\s+on[a-zA-Z0-9_-]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace('/href\s*=\s*(?:"|\')?\s*(?:javascript:|data:)[^\s"\'>]+/i', '', $html) ?? $html;
        $html = preg_replace('/src\s*=\s*(?:"|\')?\s*(?:javascript:|data:)[^\s"\'>]+/i', '', $html) ?? $html;

        return strip_tags($html, $allowedTags);
    }
}
