<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway;

use Facturacion\Domain\Exception\TransientTransmissionException;

/**
 * Base para los gateways de pais. Aporta el "Template Method" de
 * transmision con reintentos (backoff exponencial) que TODOS los paises
 * comparten, dejando a cada subclase solo las diferencias locales:
 * construccion del XML, firma y llamada concreta a la autoridad.
 */
abstract class AbstractFiscalGateway
{
    /** Reintenta operaciones marcadas como transitorias con backoff exponencial. */
    protected function withRetries(callable $operation, int $maxAttempts = 4, int $baseDelayMs = 500): mixed
    {
        $attempt = 0;
        beginning:
        try {
            $attempt++;
            return $operation($attempt);
        } catch (TransientTransmissionException $e) {
            if ($attempt >= $maxAttempts) {
                throw $e; // el worker lo llevara a la dead-letter queue
            }
            // backoff exponencial con jitter
            $delay = (int) ($baseDelayMs * (2 ** ($attempt - 1)) + random_int(0, 250));
            usleep($delay * 1000);
            goto beginning;
        }
    }
}
