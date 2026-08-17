<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Referencia a un comprobante previo (para notas de credito/debito o consultas).
 */
final class DocumentReference
{
    public function __construct(
        public readonly CountryCode  $country,
        public readonly DocumentType $type,
        public readonly string       $series,
        public readonly string       $number,
        public readonly ?string      $externalId = null // CUFE/CAE/UUID del doc referido
    ) {
    }
}
