<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Tipo de comprobante en terminos CANONICOS (agnostico de pais).
 * Cada Gateway traduce este valor al catalogo local
 * (ej. PE catalogo 01=Factura, 03=Boleta, 07=Nota Credito;
 *      MX tipoDeComprobante I=Ingreso, E=Egreso; etc.).
 */
enum DocumentType: string
{
    case FACTURA          = 'factura';
    case BOLETA           = 'boleta';            // consumidor final (PE boleta / CL boleta / etc.)
    case NOTA_CREDITO     = 'nota_credito';
    case NOTA_DEBITO      = 'nota_debito';
}
