<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use TracksUserChanges;

    protected $fillable = ['name', 'permissions'];

    protected $casts = ['permissions' => 'array'];
}
