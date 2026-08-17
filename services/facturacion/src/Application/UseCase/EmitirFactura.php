<?php

declare(strict_types=1);

namespace Facturacion\Application\UseCase;

use Facturacion\Application\FiscalGatewayFactory;
use Facturacion\Domain\Exception\FiscalException;
use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\DocumentStatus;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Domain\Port\DocumentRepository;
use Facturacion\Domain\Result\EmissionResult;

/**
 * ===================================================================
 *  CASO DE USO: Emitir Factura
 * ===================================================================
 * Orquesta el flujo independientemente del pais:
 *   1) idempotencia (evita doble emision)
 *   2) persiste PENDIENTE + auditoria
 *   3) resuelve la estrategia de pais (Strategy)
 *   4) delega la emision al Gateway (genera XML, firma, transmite)
 *   5) persiste resultado + archivos + auditoria
 *
 * No conoce SUNAT/DIAN/etc.: toda la especificidad vive en el Gateway.
 */
final class EmitirFactura
{
    public function __construct(
        private readonly FiscalGatewayFactory $gateways,
        private readonly DocumentRepository $repository,
        private readonly AuditLogger $audit
    ) {
    }

    public function handle(CanonicalDocument $document, string $idempotencyKey): EmissionResult
    {
        // 1) Idempotencia: si ya se proceso esta clave, devolver lo existente.
        if ($existing = $this->repository->findByIdempotencyKey($idempotencyKey)) {
            return $existing['result'];
        }

        // 2) Persistir en estado inicial + auditar la recepcion.
        $documentId = $this->repository->create($document, $idempotencyKey);
        $this->audit->record($documentId, 'received', [
            'country' => $document->country->value,
            'doc'     => $document->fullId(),
            'hash'    => hash('sha256', serialize($document)),
        ]);

        try {
            $this->repository->updateStatus($documentId, DocumentStatus::ENVIANDO);

            // 3) Strategy: obtener el adaptador del pais.
            $gateway = $this->gateways->for($document->country);

            // 4) Delegar: el Gateway genera XML local, firma y transmite.
            $result = $gateway->emitirFactura($document);

            // 5) Persistir resultado y artefactos legales.
            $this->repository->updateStatus($documentId, $result->status, [
                'external_id'  => $result->externalId,
                'observations' => $result->observations,
            ]);
            $this->repository->attachFiles($documentId, array_filter([
                'xml'      => $result->xmlPath,
                'response' => $result->responsePath,
                'pdf'      => $result->pdfPath,
            ]));
            $this->audit->record($documentId, 'response', [
                'status'  => $result->status->value,
                'code'    => $result->authorityCode,
                'message' => $result->authorityMessage,
            ]);

            return $result;
        } catch (FiscalException $e) {
            // Clasifica el error: reintentable => queda ERROR para el worker;
            // no reintentable => RECHAZADO (correccion manual).
            $status = $e->esReintentable() ? DocumentStatus::ERROR : DocumentStatus::RECHAZADO;
            $this->repository->updateStatus($documentId, $status, ['error' => $e->getMessage()]);
            $this->audit->record($documentId, 'error', [
                'reintentable' => $e->esReintentable(),
                'message'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
