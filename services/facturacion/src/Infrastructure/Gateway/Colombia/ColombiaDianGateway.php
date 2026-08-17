<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Colombia;

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
 * ADAPTADOR COLOMBIA - DIAN.  (Fase 3)
 * Formato: UBL 2.1 con extensiones DIAN. Identificador: CUFE (facturas) / CUDE (notas).
 * Modelo: PRE-clearance (DIAN VALIDA antes de entregar al comprador).
 * Transporte: web service SOAP de la DIAN (recepcion + consulta de estado).
 *
 * Para habilitar Colombia solo se implementa esta clase y se registra en
 * config/facturacion.php. Nada mas del sistema cambia.
 */
final class ColombiaDianGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(private readonly array $config)
    {
    }

    public function country(): CountryCode
    {
        return CountryCode::CO;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        // TODO: construir UBL 2.1 DIAN, calcular CUFE, firmar, enviar y esperar
        // validacion previa; mapear ApplicationResponse a EmissionResult.
        throw new \RuntimeException('Colombia (DIAN) pendiente - Fase 3.');
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        throw new \RuntimeException('Colombia (DIAN) pendiente - Fase 3.');
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        // CO no "anula": se emite Nota de Credito que referencia la factura.
        throw new \RuntimeException('Colombia (DIAN) pendiente - Fase 3.');
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        return new StatusResult(DocumentStatus::PENDIENTE, $reference->externalId);
    }
}
