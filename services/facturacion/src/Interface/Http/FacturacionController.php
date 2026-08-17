<?php

declare(strict_types=1);

namespace Facturacion\Interface\Http;

use Facturacion\Application\UseCase\AnularFactura;
use Facturacion\Application\UseCase\ConsultarEstado;
use Facturacion\Application\UseCase\EmitirFactura;
use Facturacion\Application\UseCase\EmitirNotaCredito;

/**
 * ===================================================================
 *  API REST del servicio de facturacion (lo que consume el ERP)
 * ===================================================================
 * Contrato estable y agnostico de pais. El ERP nunca habla con SUNAT/DIAN:
 * solo con estos endpoints.
 *
 *   POST /api/v1/documents                 -> emitir factura/boleta
 *   POST /api/v1/documents/{id}/credit-note -> emitir nota de credito
 *   POST /api/v1/documents/{id}/void        -> anular / dar de baja
 *   GET  /api/v1/documents/{id}             -> consultar estado
 *
 * La cabecera 'Idempotency-Key' evita dobles emisiones ante reintentos de red.
 * Este controlador es framework-agnostico a proposito; adaptarlo a las
 * Request/Response de Laravel es trivial (inyeccion + validacion FormRequest).
 */
final class FacturacionController
{
    public function __construct(
        private readonly EmitirFactura $emitir,
        private readonly EmitirNotaCredito $emitirNC,
        private readonly AnularFactura $anular,
        private readonly ConsultarEstado $consultar,
        private readonly DocumentAssembler $assembler // arma CanonicalDocument desde el JSON
    ) {
    }

    /** POST /api/v1/documents */
    public function store(array $payload, string $idempotencyKey): array
    {
        $document = $this->assembler->fromArray($payload);
        $result   = $this->emitir->handle($document, $idempotencyKey);

        return [
            'status'       => $result->status->value,
            'external_id'  => $result->externalId,
            'observations' => $result->observations,
            'files'        => array_filter([
                'xml' => $result->xmlPath,
                'cdr' => $result->responsePath,
                'pdf' => $result->pdfPath,
            ]),
        ];
    }

    /** POST /api/v1/documents/{id}/credit-note */
    public function creditNote(array $payload, string $idempotencyKey): array
    {
        $nc = $this->assembler->fromArray($payload);
        $r  = $this->emitirNC->handle($nc, $idempotencyKey);
        return ['status' => $r->status->value, 'external_id' => $r->externalId];
    }

    /** POST /api/v1/documents/{id}/void */
    public function void(array $reference, string $reason): array
    {
        $r = $this->anular->handle($this->assembler->referenceFromArray($reference), $reason);
        return ['status' => $r->status->value, 'ticket' => $r->ticket];
    }

    /** GET /api/v1/documents/{id} */
    public function show(array $reference): array
    {
        $r = $this->consultar->handle($this->assembler->referenceFromArray($reference));
        return ['status' => $r->status->value, 'external_id' => $r->externalId, 'message' => $r->authorityMessage];
    }
}
