<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Emisor o receptor de un comprobante, en forma canonica.
 * El tipo/numero de documento se interpreta segun el pais en cada Gateway
 * (PE: RUC/DNI; CO: NIT/CC; CL: RUT; AR: CUIT; MX: RFC).
 */
final class Party
{
    public function __construct(
        public readonly string $docType,   // canonico: "tax_id" | "national_id" | "foreign_id"
        public readonly string $docNumber, // RUC / NIT / RUT / CUIT / RFC ...
        public readonly string $legalName,
        public readonly ?string $address = null,
        public readonly ?string $email = null,
        public readonly ?string $taxRegime = null // requerido en MX (RegimenFiscal), opcional en otros
    ) {
    }
}
