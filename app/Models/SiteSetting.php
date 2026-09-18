<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    use TracksUserChanges;

    protected $fillable = ['key', 'value', 'type', 'group', 'label', 'sort_order'];

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::where('key', $key)->first()?->update(['value' => $value]);
    }

    /**
     * Get all settings as a key=>value array, optionally filtered by group.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)
            ->orderBy('sort_order')
            ->pluck('value', 'key')
            ->toArray();
    }
}
