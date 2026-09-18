<?php

namespace App\Models;

use App\Models\Concerns\TracksUserChanges;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use TracksUserChanges;

    protected $fillable = ['guest_name', 'email', 'phone', 'category', 'message', 'status', 'read_at'];

    protected $casts = ['read_at' => 'datetime'];

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function markAsRead(): void
    {
        $this->update(['status' => 'read', 'read_at' => now()]);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'new'      => 'bg-amber-500/15 text-amber-400 ring-amber-400/20',
            'read'     => 'bg-sky-500/15 text-sky-400 ring-sky-400/20',
            'replied'  => 'bg-emerald-500/15 text-emerald-400 ring-emerald-400/20',
            'archived' => 'bg-gray-500/15 text-gray-400 ring-gray-400/20',
            default    => 'bg-gray-500/15 text-gray-400',
        };
    }
}
