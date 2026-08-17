<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'doc_type', 'doc_number', 'phone', 'email', 'address', 'birthday', 'is_active',
    ];

    protected $casts = [
        'birthday' => 'date',
        'is_active' => 'boolean',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
