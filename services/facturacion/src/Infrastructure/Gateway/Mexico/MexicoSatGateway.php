<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Mexico;

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
 * ADAPTADOR MEXICO - SAT.  (Fase 6)
 * Formato: CFDI 4.0 (XML propio del SAT). Sello del emisor (CSD).
 * Timbrado: obligatorio via PAC (Proveedor Autorizado de Certificacion),
 *           que devuelve el UUID (folio fiscal) y el Timbre Fiscal Digital.
 * Modelo: PRE-clearance (PAC valida en tiempo real; datos del receptor
 *         deben coincidir EXACTO con el RFC del SAT o se rechaza).
 */
final class MexicoSatGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function country(): CountryCode
    {
        return CountryCode::MX;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        // TODO: armar CFDI 4.0 (UsoCFDI, RegimenFiscal, MetodoPago), sellar con CSD,
        // timbrar via PAC, capturar UUID; mapear a EmissionResult (externalId = UUID).
        throw new \RuntimeException('Mexico (SAT/CFDI 4.0) pendiente - Fase 6.');
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        throw new \RuntimeException('Mexico (SAT/CFDI 4.0) pendiente - Fase 6.');
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        // MX: cancelacion CFDI con motivo (01-04) y posible acuse/aceptacion del receptor.
        throw new \RuntimeException('Mexico (SAT/CFDI 4.0) pendiente - Fase 6.');
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        return new StatusResult(DocumentStatus::PENDIENTE, $reference->externalId);
    }
}
