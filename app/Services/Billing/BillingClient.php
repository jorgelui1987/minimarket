<?php

declare(strict_types=1);

namespace App\Services\Billing;

/**
 * ===================================================================
 *  PUERTO DEL ERP hacia el servicio de facturacion
 * ===================================================================
 * El ERP (POS, Ventas, Devoluciones) depende SOLO de esta interfaz.
 * Asi puede cambiarse la implementacion (REST hoy, mensajeria manana)
 * sin tocar la logica de negocio del minimarket.
 */
interface BillingClient
{
    /** Emite una factura/boleta a partir de una venta del ERP. Devuelve el estado y el id externo. */
    public function emitir(array $documentPayload, string $idempotencyKey): BillingResult;

    /** Emite una nota de credito (usado por el modulo Devoluciones). */
    public function emitirNotaCredito(array $creditNotePayload, string $idempotencyKey): BillingResult;

    /** Anula / da de baja un comprobante. */
    public function anular(array $reference, string $reason): BillingResult;

    /** Consulta el estado actual de un comprobante. */
    public function estado(array $reference): BillingResult;
}
