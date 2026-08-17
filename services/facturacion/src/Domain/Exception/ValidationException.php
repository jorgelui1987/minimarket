<?php

declare(strict_types=1);

namespace Facturacion\Domain\Exception;

/**
 * Datos invalidos ANTES de transmitir (falla rapido, no se reintenta).
 * Ej: RUC mal formado, total no cuadra con lineas, falta RegimenFiscal en MX.
 */
final class ValidationException extends FiscalException
{
    /** @param string[] $errors */
    public function __construct(public readonly array $errors, string $message = 'Documento invalido')
    {
        parent::__construct($message);
    }

    public function esReintentable(): bool
    {
        return false;
    }
}
