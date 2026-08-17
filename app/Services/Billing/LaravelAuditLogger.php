<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Facturacion\Domain\Port\AuditLogger;
use Illuminate\Support\Facades\Log;

/**
 * Implementación del AuditLogger del servicio de facturación usando el logging
 * de Laravel. Es suficiente para el driver in-process; en Fase 2 se sustituye
 * por auditoría persistente en base de datos.
 */
final class LaravelAuditLogger implements AuditLogger
{
    public function record(string $documentId, string $event, array $context = [], ?string $actor = null): void
    {
        Log::channel(config('logging.default'))->info('[facturacion] ' . $event, [
            'document' => $documentId,
            'actor'    => $actor,
            'context'  => $context,
        ]);
    }
}
