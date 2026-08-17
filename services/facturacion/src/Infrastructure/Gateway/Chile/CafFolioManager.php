<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Model\DocumentType;

/**
 * Gestor de folios CAF (especifico de Chile). Reserva correlativos de un
 * rango autorizado por el SII, avisa cuando quedan pocos y bloquea la
 * emision si no hay CAF vigente. Encapsulado aqui para no filtrar la
 * particularidad chilena al resto del sistema.
 */
final class CafFolioManager
{
    public function next(DocumentType $type): int
    {
        // TODO: leer CAF vigente para el tipo, reservar siguiente folio atomicamente,
        // emitir alerta si el saldo < umbral, error si esta agotado/vencido.
        throw new \RuntimeException('Gestor de folios CAF pendiente - Fase 4.');
    }
}
