<?php

declare(strict_types=1);

namespace Facturacion\Domain\Port;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Result\AnnulmentResult;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Domain\Result\StatusResult;

/**
 * ===================================================================
 *  PUERTO (Strategy)  -  Contrato comun de facturacion por pais
 * ===================================================================
 * Esta es la interfaz que pediste: EmitirFactura, AnularFactura,
 * EmitirNotaCredito, ConsultarEstado. Cada pais la implementa una vez
 * en Infrastructure\Gateway\<Pais>.
 *
 * REGLA DE ORO (Open/Closed): agregar Colombia, Chile, Argentina o
 * Mexico = crear una clase nueva que implemente esta interfaz y
 * registrarla en config/facturacion.php. NINGUN codigo existente
 * (ERP, use cases, otros gateways) se toca.
 */
interface FiscalGateway
{
    /** Emite una factura o boleta y devuelve el resultado normalizado. */
    public function emitirFactura(CanonicalDocument $document): EmissionResult;

    /** Emite una nota de credito (devoluciones, anulaciones parciales, descuentos). */
    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult;

    /** Anula / da de baja un comprobante ya emitido. */
    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult;

    /** Consulta el estado de un comprobante ante la autoridad fiscal. */
    public function consultarEstado(DocumentReference $reference): StatusResult;

    /** Codigo de pais que este gateway atiende (para el registro/factory). */
    public function country(): \Facturacion\Domain\Model\CountryCode;
}
