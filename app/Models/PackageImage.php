<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageImage extends Model
{
    use TracksUserChanges;

    protected $fillable = ['package_id', 'image_path', 'caption', 'sort_order'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
