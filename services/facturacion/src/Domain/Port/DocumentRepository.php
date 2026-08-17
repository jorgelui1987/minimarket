<?php

declare(strict_types=1);

namespace Facturacion\Domain\Port;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\DocumentStatus;

/**
 * Persistencia del comprobante y su maquina de estados (puerto de salida).
 * La implementacion concreta (Eloquent/PDO) vive en Infrastructure.
 */
interface DocumentRepository
{
    /** Crea el registro en estado PENDIENTE y devuelve su id interno. */
    public function create(CanonicalDocument $document, string $idempotencyKey): string;

    public function findByIdempotencyKey(string $idempotencyKey): ?array;

    public function updateStatus(string $documentId, DocumentStatus $status, array $meta = []): void;

    public function attachFiles(string $documentId, array $paths): void;
}
