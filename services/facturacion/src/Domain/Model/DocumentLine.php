<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Linea de detalle canonica de un comprobante.
 */
final class DocumentLine
{
    /**
     * @param Tax[] $taxes
     */
    public function __construct(
        public readonly string $sku,
        public readonly string $description,
        public readonly float  $quantity,
        public readonly string $unit,        // NIU, ZZ, KGM ... (UN/ECE si aplica)
        public readonly Money  $unitPrice,   // sin impuestos
        public readonly Money  $lineTotal,   // sin impuestos
        public readonly array  $taxes = []
    ) {
    }
}
