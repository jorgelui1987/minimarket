<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Estado del comprobante dentro de la maquina de estados del servicio.
 * Es agnostico de pais; cada Gateway mapea la respuesta local a estos valores.
 */
enum DocumentStatus: string
{
    case PENDIENTE   = 'pendiente';    // creado, aun no transmitido
    case ENVIANDO    = 'enviando';     // en cola / transmitiendo a la autoridad
    case ACEPTADO    = 'aceptado';     // CDR aceptado / CAE / UUID / autorizado
    case OBSERVADO   = 'observado';    // aceptado con observaciones (PE)
    case RECHAZADO   = 'rechazado';    // rechazo de negocio (no reintentar, corregir)
    case ANULADO     = 'anulado';      // baja / anulacion aceptada
    case ERROR       = 'error';        // fallo tecnico transitorio (reintentable)

    public function esFinal(): bool
    {
        return in_array($this, [self::ACEPTADO, self::OBSERVADO, self::RECHAZADO, self::ANULADO], true);
    }

    public function esReintentable(): bool
    {
        return $this === self::ERROR;
    }
}
