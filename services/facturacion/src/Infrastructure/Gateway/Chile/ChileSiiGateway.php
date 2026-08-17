<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

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
 * ADAPTADOR CHILE - SII.  (Fase 4)
 * Formato: DTE (XML propio del SII), no UBL. Timbre Electronico (TED).
 * Folios: CAF (Codigo de Autorizacion de Folios) que el contribuyente
 *         solicita y ADMINISTRA; sin CAF vigente no se puede emitir.
 * Modelo: SELF-clearance (se emite con folio autorizado; luego envio + estado por trackid).
 *
 * Reto especifico: gestor de folios CAF (reserva de correlativos, alerta
 * de agotamiento, renovacion). Se resuelve dentro de este adaptador.
 */
final class ChileSiiGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(
        private readonly CafFolioManager $folios,
        private readonly array $config
    ) {
    }

    public function country(): CountryCode
    {
        return CountryCode::CL;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        // $folio = $this->folios->next($document->type);  // reserva folio del CAF
        // TODO: armar DTE, timbrar (TED con CAF), firmar, EnvioDTE, capturar trackid.
        throw new \RuntimeException('Chile (SII) pendiente - Fase 4.');
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        throw new \RuntimeException('Chile (SII) pendiente - Fase 4.');
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        throw new \RuntimeException('Chile (SII) pendiente - Fase 4.');
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        return new StatusResult(DocumentStatus::PENDIENTE, $reference->externalId);
    }
}
