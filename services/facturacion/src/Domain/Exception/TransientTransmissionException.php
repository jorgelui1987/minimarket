<?php

declare(strict_types=1);

namespace Facturacion\Domain\Exception;

/**
 * Fallo TECNICO transitorio (timeout, 5xx, WS de la autoridad caido,
 * error de red). SI se reintenta con backoff exponencial; tras agotar
 * intentos pasa a la dead-letter queue.
 */
final class TransientTransmissionException extends FiscalException
{
    public function esReintentable(): bool
    {
        return true;
    }
}
