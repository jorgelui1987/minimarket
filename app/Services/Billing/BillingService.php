<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ElectronicDocument;
use App\Models\Sale;

/**
 * Orquesta la emisión del comprobante electrónico de una venta desde el ERP.
 * Registra el estado en electronic_documents y NUNCA rompe la venta: si el
 * servicio falla, el comprobante queda en 'error'/'pendiente' y puede
 * reintentarse desde la UI.
 */
final class BillingService
{
    public function __construct(
        private readonly BillingClient $client
    ) {
    }

    /** Documentos que sí generan comprobante electrónico (no el 'ticket' interno). */
    public function facturable(Sale $sale): bool
    {
        return in_array($sale->document_type, ['boleta', 'factura'], true);
    }

    public function emitForSale(Sale $sale): ?ElectronicDocument
    {
        if (!$this->facturable($sale)) {
            return null;
        }

        $idempotencyKey = "sale-{$sale->tenant_id}-{$sale->id}";
        $country = BillingSettings::country() ?: 'PE';

        $doc = ElectronicDocument::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'sale_id'       => $sale->id,
                'country'       => $country,
                'document_type' => $sale->document_type,
                'series'        => $sale->series,
                'number'        => $sale->number,
                'status'        => 'pendiente',
            ]
        );

        // Idempotencia: si ya fue aceptado, no reemitir.
        if ($doc->aceptado()) {
            return $doc;
        }

        try {
            $payload = $this->mapper($country)->toPayload($sale);
            $result = $this->client->emitir($payload, $idempotencyKey);

            $doc->update([
                'status'         => $result->status,
                'external_id'    => $result->externalId,
                'message'        => $result->message,
                'observations'   => $result->observations,
                'xml_path'       => $result->files['xml'] ?? null,
                'cdr_path'       => $result->files['cdr'] ?? null,
            ]);
        } catch (\Throwable $e) {
            $doc->update(['status' => 'error', 'message' => $e->getMessage()]);
            report($e);
        }

        return $doc->refresh();
    }

    /** Selecciona el mapper según el país fiscal activo. */
    private function mapper(string $country): SaleBillingMapper|ChileSaleBillingMapper
    {
        return $country === 'CL'
            ? new ChileSaleBillingMapper()
            : new SaleBillingMapper();
    }
}