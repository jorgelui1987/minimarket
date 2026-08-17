<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Value Object monetario inmutable. Evita errores de redondeo trabajando
 * en la unidad menor no; mantiene el monto como string decimal para precision.
 */
final class Money
{
    public function __construct(
        public readonly string $amount,   // ej. "150.00"
        public readonly string $currency  // ISO-4217: PEN, COP, CLP, ARS, MXN, USD
    ) {
    }

    public static function of(float|string $amount, string $currency): self
    {
        return new self(number_format((float) $amount, 2, '.', ''), strtoupper($currency));
    }

    public function toFloat(): float
    {
        return (float) $this->amount;
    }
}
