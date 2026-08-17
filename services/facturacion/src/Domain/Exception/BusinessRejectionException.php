<?php

declare(strict_types=1);

namespace Facturacion\Domain\Exception;

/**
 * La autoridad RECHAZO el comprobante por reglas de negocio (CDR rechazado,
 * error 4xx SUNAT, rechazo DIAN/SAT). NO se reintenta: requiere correccion.
 */
final class BusinessRejectionException extends FiscalException
{
    public function __construct(
        public readonly string $authorityCode,
        string $message
    ) {
        parent::__construct($message);
    }

    public function esReintentable(): bool
    {
        return false;
    }
}
