<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'ruc', 'plan_id', 'status', 'contact_email', 'contact_phone',
        'trial_ends_at', 'subscription_ends_at', 'is_active',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Fecha de vencimiento efectiva (prueba o suscripción). */
    public function endsAt(): ?Carbon
    {
        return $this->status === 'trial' ? $this->trial_ends_at : $this->subscription_ends_at;
    }

    public function daysLeft(): ?int
    {
        $end = $this->endsAt();

        return $end ? (int) ceil(Carbon::now()->floatDiffInDays($end, false)) : null;
    }

    public function isExpired(): bool
    {
        if (! $this->is_active || $this->status === 'suspended') {
            return true;
        }

        $end = $this->endsAt();

        return $end !== null && $end->isPast();
    }

    public function reachedProductLimit(int $current): bool
    {
        $max = $this->plan->max_products ?? -1;

        return $max >= 0 && $current >= $max;
    }

    public function reachedUserLimit(int $current): bool
    {
        $max = $this->plan->max_users ?? -1;

        return $max >= 0 && $current >= $max;
    }

    public function statusLabel(): string
    {
        if ($this->isExpired()) {
            return 'Vencida';
        }

        return match ($this->status) {
            'trial' => 'En prueba',
            'active' => 'Activa',
            'suspended' => 'Suspendida',
            default => ucfirst($this->status),
        };
    }
}
