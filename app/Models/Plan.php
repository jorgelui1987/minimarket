<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'max_products', 'max_users', 'max_monthly_sales', 'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function limitLabel(int $value): string
    {
        return $value < 0 ? 'Ilimitado' : (string) $value;
    }
}
