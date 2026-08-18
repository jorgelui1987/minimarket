<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Sale;
use App\Services\Billing\BillingSettings;

/**
 * Convierte una Venta del ERP en el payload CANÓNICO para Chile (SII).
 * En el POS el precio INCLUYE IVA (19%), por lo que aquí se desagrega a
 * base + impuesto, como exige el modelo canónico del servicio.
 */
final class ChileSaleBillingMapper
{
    private const IVA = 0.19;

    public function toPayload(Sale $sale): array
    {
        $sale->loadMissing(['items', 'customer']);
        $cl = BillingSettings::cl();

        return [
            'country'   => 'CL',
            'type'      => $sale->document_type,           // boleta | factura
            'series'    => $sale->series,
            'number'    => $sale->number,
            'issued_at' => ($sale->created_at ?? now())->toIso8601String(),
            'currency'  => 'CLP',
            'issuer'    => $this->issuer($cl),
            'customer'  => $this->customer($sale),
            'lines'     => $this->lines($sale),
            'subtotal'  => (float) $sale->subtotal,         // base sin IVA
            'total'     => (float) $sale->total,            // con IVA
        ];
    }

    private function issuer(array $cl): array
    {
        return [
            'doc_type'   => 'tax_id',
            'doc_number' => (string) ($cl['rut_emisor'] ?? ''),
            'legal_name' => (string) ($cl['razon_social'] ?? ''),
            'address'    => $cl['direccion'] ?? '',
            'email'      => null,
            'tax_regime' => $cl['giro'] ?? '',
        ];
    }

    private function customer(Sale $sale): array
    {
        $c = $sale->customer;

        // Boleta admite consumidor final; factura requiere RUT.
        if (!$c) {
            return ['doc_type' => 'sin_doc', 'doc_number' => '66666666-6', 'legal_name' => 'CONSUMIDOR FINAL'];
        }

        $docType = match ($c->doc_type) {
            'RUT'       => 'tax_id',
            'DNI', 'RUT' => 'national_id',
            'CE', 'PAS' => 'foreign_id',
            default    => $sale->document_type === 'factura' ? 'tax_id' : 'national_id',
        };

        return [
            'doc_type'   => $docType,
            'doc_number' => (string) ($c->doc_number ?: '66666666-6'),
            'legal_name' => (string) $c->name,
            'address'    => $c->address,
            'email'      => $c->email,
        ];
    }

    private function lines(Sale $sale): array
    {
        return $sale->items->map(function ($item) {
            $lineTotalWithIva = (float) $item->subtotal;                 // con IVA
            $lineBase = round($lineTotalWithIva / (1 + self::IVA), 2);   // sin IVA
            $unitBase = round(((float) $item->price) / (1 + self::IVA), 2);
            $ivaAmount = round($lineTotalWithIva - $lineBase, 2);

            return [
                'sku'         => (string) ($item->product?->barcode ?? $item->product_id),
                'description' => (string) $item->product_name,
                'quantity'    => (float) $item->quantity,
                'unit'        => 'UN',
                'unit_price'  => $unitBase,   // sin IVA
                'line_total'  => $lineBase,   // sin IVA
                'taxes'       => [[
                    'kind'   => 'vat',
                    'rate'   => 19.0,
                    'amount' => $ivaAmount,
                ]],
            ];
        })->all();
    }
}