<?php

declare(strict_types=1);

namespace App\Services\Billing;

/**
 * Implementación por defecto cuando el servicio de facturación aún no está
 * configurado/desplegado. No emite: deja el comprobante en 'pendiente' con un
 * mensaje claro. Permite que el ERP funcione y que la integración sea visible
 * sin romper la venta. Al configurar el servicio real, el binding cambia a
 * RestBillingClient (ver AppServiceProvider).
 */
final class NullBillingClient implements BillingClient
{
    private function pendiente(): BillingResult
    {
        return new BillingResult(
            status: 'pendiente',
            message: 'Servicio de facturación electrónica no configurado (BILLING_ENABLED=false).'
        );
    }

    public function emitir(array $documentPayload, string $idempotencyKey): BillingResult
    {
        return $this->pendiente();
    }

    public function emitirNotaCredito(array $creditNotePayload, string $idempotencyKey): BillingResult
    {
        return $this->pendiente();
    }

    public function anular(array $reference, string $reason): BillingResult
    {
        return $this->pendiente();
    }

    public function estado(array $reference): BillingResult
    {
        return $this->pendiente();
    }
}
