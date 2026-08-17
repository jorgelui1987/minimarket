<?php

declare(strict_types=1);

namespace Facturacion\Application\UseCase;

use Facturacion\Application\FiscalGatewayFactory;
use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Domain\Port\DocumentRepository;
use Facturacion\Domain\Result\EmissionResult;

/**
 * CASO DE USO: Emitir Nota de Credito (devoluciones, descuentos, anulacion por NC).
 * Requiere que CanonicalDocument->references apunte al comprobante original.
 */
final class EmitirNotaCredito
{
    public function __construct(
        private readonly FiscalGatewayFactory $gateways,
        private readonly DocumentRepository $repository,
        private readonly AuditLogger $audit
    ) {
    }

    public function handle(CanonicalDocument $creditNote, string $idempotencyKey): EmissionResult
    {
        if ($existing = $this->repository->findByIdempotencyKey($idempotencyKey)) {
            return $existing['result'];
        }
        $id = $this->repository->create($creditNote, $idempotencyKey);
        $this->audit->record($id, 'received', ['tipo' => 'nota_credito', 'ref' => $creditNote->references?->number]);

        $gateway = $this->gateways->for($creditNote->country);
        $result = $gateway->emitirNotaCredito($creditNote);

        $this->repository->updateStatus($id, $result->status, ['external_id' => $result->externalId]);
        $this->repository->attachFiles($id, array_filter([
            'xml' => $result->xmlPath, 'response' => $result->responsePath, 'pdf' => $result->pdfPath,
        ]));
        $this->audit->record($id, 'response', ['status' => $result->status->value]);

        return $result;
    }
}
