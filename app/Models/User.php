<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Support\Permissions;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    public function roleDefinition(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function hasPermission(string $module): bool
    {
        if (!$this->is_active || !isset(Permissions::MODULES[$module])) return false;
        if ($this->isSuperAdmin()) return true;
        return isset(Permissions::ROLES[$this->role]) && in_array($module, $this->roleDefinition?->permissions ?? [], true);
    }

    public function canAccessAdminRoute(?string $route): bool
    {
        if (!$this->is_active || !isset(Permissions::ROLES[$this->role]) || !str_starts_with($route ?? '', 'admin.')) return false;
        $module = explode('.', $route)[1] ?? '';
        if ($module === 'dashboard') return true;
        if (in_array($module, ['users', 'roles'], true)) return $this->isSuperAdmin();
        return $this->hasPermission($module);
    }

    public function getRoleLabelAttribute(): string
    {
        return Permissions::ROLES[$this->role] ?? 'Unknown role';
    }
}
