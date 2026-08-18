<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DteLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'electronic_document_id', 'document_id', 'event', 'context', 'actor',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function electronicDocument(): BelongsTo
    {
        return $this->belongsTo(ElectronicDocument::class);
    }
}