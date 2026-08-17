<?php

declare(strict_types=1);

namespace Facturacion\Domain\Result;

use Facturacion\Domain\Model\DocumentStatus;

/**
 * Resultado de una emision, normalizado para cualquier pais.
 * externalId contiene el identificador fiscal local:
 *   PE -> hash del CDR / nombre del comprobante ; CO -> CUFE ;
 *   CL -> track id / folio ; AR -> CAE ; MX -> UUID (folio fiscal).
 */
final class EmissionResult
{
    /**
     * @param string[] $observations
     */
    public function __construct(
        public readonly DocumentStatus $status,
        public readonly ?string $externalId = null,
        public readonly ?string $xmlPath = null,
        public readonly ?string $responsePath = null, // CDR / acuse / respuesta autoridad
        public readonly ?string $pdfPath = null,
        public readonly array $observations = [],
        public readonly ?string $authorityCode = null,   // codigo de respuesta local
        public readonly ?string $authorityMessage = null
    ) {
    }
}
