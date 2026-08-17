<?php

declare(strict_types=1);

namespace Facturacion\Domain\Port;

/**
 * Bitacora de auditoria append-only. Cada transicion de estado y cada
 * interaccion con la autoridad debe registrarse (quien, cuando, payload hash).
 */
interface AuditLogger
{
    public function record(
        string $documentId,
        string $event,          // received, generated, signed, transmitted, response, retry, error
        array $context = [],    // incluye hash de payload, codigo autoridad, intento #
        ?string $actor = null
    ): void;
}
