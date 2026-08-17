<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Impuesto aplicado a una linea o al total, en forma canonica.
 * "kind" es el concepto agnostico; cada Gateway lo mapea al codigo local
 * (PE IGV, CO IVA, CL IVA, AR IVA, MX IVA/IEPS).
 */
final class Tax
{
    public function __construct(
        public readonly string $kind,   // "vat" | "excise" | "exempt" | "inaffect"
        public readonly float  $rate,   // ej. 18.0, 19.0, 16.0, 21.0
        public readonly Money  $amount
    ) {
    }
}
