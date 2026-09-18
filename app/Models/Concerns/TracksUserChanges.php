<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait TracksUserChanges
{
    public static function bootTracksUserChanges(): void
    {
        static::creating(function (Model $model) {
            // Always derive the actor on the server; never accept audit IDs from form data.
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function (Model $model) {
            $model->created_by = $model->getRawOriginal('created_by');
            // Authentication rotates remember tokens; that is not an account edit.
            if ($model instanceof User && array_keys($model->getDirty()) === ['remember_token']) return;
            $model->updated_by = Auth::id();
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
