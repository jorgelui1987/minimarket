<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectronicDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'sale_id', 'country', 'document_type', 'series', 'number',
        'status', 'external_id', 'authority_code', 'message', 'observations',
        'xml_path', 'cdr_path', 'idempotency_key',
    ];

    protected $casts = [
        'observations' => 'array',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /** ¿El comprobante fue aceptado (con o sin observaciones)? */
    public function aceptado(): bool
    {
        return in_array($this->status, ['aceptado', 'observado'], true);
    }

    /** ¿Puede reintentarse la emisión? */
    public function reintentable(): bool
    {
        return in_array($this->status, ['pendiente', 'error'], true);
    }

    public function estadoLabel(): string
    {
        return match ($this->status) {
            'aceptado'  => 'Aceptado',
            'observado' => 'Observado',
            'rechazado' => 'Rechazado',
            'anulado'   => 'Anulado',
            'enviando'  => 'Enviando',
            'error'     => 'Error',
            default     => 'Pendiente',
        };
    }

    /** Clases Tailwind para el badge según estado. */
    public function estadoColor(): string
    {
        return match ($this->status) {
            'aceptado'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'observado' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'rechazado' => 'bg-rose-50 text-rose-700 ring-rose-200',
            'anulado'   => 'bg-slate-100 text-slate-600 ring-slate-200',
            'enviando'  => 'bg-sky-50 text-sky-700 ring-sky-200',
            'error'     => 'bg-rose-50 text-rose-700 ring-rose-200',
            default     => 'bg-slate-50 text-slate-500 ring-slate-200',
        };
    }
}
