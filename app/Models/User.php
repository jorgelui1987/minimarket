<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BelongsToTenant, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_super_admin',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_super_admin' => 'boolean',
        ];
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            'admin' => 'Administrador',
            'cajero' => 'Cajero',
            'almacen' => 'Almacén',
            default => 'Usuario',
        };
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }
}
