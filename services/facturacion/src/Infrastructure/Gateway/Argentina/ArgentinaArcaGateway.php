<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Argentina;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Model\DocumentStatus;
use Facturacion\Domain\Port\FiscalGateway;
use Facturacion\Domain\Result\AnnulmentResult;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Domain\Result\StatusResult;
use Facturacion\Infrastructure\Gateway\AbstractFiscalGateway;

/**
 * ADAPTADOR ARGENTINA - ARCA (ex-AFIP desde oct-2024).  (Fase 5)
 * Transporte: SOAP. Autenticacion WSAA (ticket TA) + WSFEv1 (comprobantes A/B/C).
 * Sin XML tipo UBL: se envian campos y ARCA devuelve un CAE (Codigo de
 * Autorizacion Electronico) con vencimiento.
 * Modelo: PRE-clearance (CAE antes de entregar).
 *
 * Valida que la abstraccion soporta un transporte MUY distinto (SOAP + CAE)
 * sin cambiar el dominio: toda la mecanica SOAP queda dentro de este adaptador.
 */
final class ArgentinaArcaGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function country(): CountryCode
    {
        return CountryCode::AR;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        // TODO: obtener ticket de acceso (WSAA), FECAESolicitar (WSFEv1),
        // capturar CAE + vencimiento; mapear a EmissionResult (externalId = CAE).
        throw new \RuntimeException('Argentina (ARCA/WSFEv1) pendiente - Fase 5.');
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        throw new \RuntimeException('Argentina (ARCA/WSFEv1) pendiente - Fase 5.');
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        // AR: se emite Nota de Credito equivalente (no hay "baja" directa).
        throw new \RuntimeException('Argentina (ARCA/WSFEv1) pendiente - Fase 5.');
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        return new StatusResult(DocumentStatus::PENDIENTE, $reference->externalId);
    }
}
