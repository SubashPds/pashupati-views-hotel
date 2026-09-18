<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomImage extends Model
{
    use TracksUserChanges;

    protected $fillable = ['room_id', 'image_path', 'caption', 'sort_order'];

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
