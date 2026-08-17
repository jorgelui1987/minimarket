<?php

declare(strict_types=1);

namespace App\Services\Billing;

/**
 * Resultado que el ERP recibe del servicio de facturacion, ya normalizado.
 */
final class BillingResult
{
    /** @param string[] $observations */
    public function __construct(
        public readonly string $status,        // pendiente|enviando|aceptado|observado|rechazado|anulado|error
        public readonly ?string $externalId = null,   // CDR/CUFE/CAE/UUID segun pais
        public readonly array $files = [],     // ['xml'=>..., 'cdr'=>..., 'pdf'=>...]
        public readonly array $observations = [],
        public readonly ?string $message = null
    ) {
    }

    public function aceptado(): bool
    {
        return in_array($this->status, ['aceptado', 'observado'], true);
    }
}
