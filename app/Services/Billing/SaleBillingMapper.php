<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Sale;
use App\Models\Setting;

/**
 * Convierte una Venta del ERP en el payload CANÓNICO que consume el servicio
 * de facturación. En el POS el precio INCLUYE IGV (18%), por lo que aquí se
 * desagrega a base + impuesto, como exige el modelo canónico.
 */
final class SaleBillingMapper
{
    private const IGV = 0.18;

    public function toPayload(Sale $sale): array
    {
        $sale->loadMissing(['items', 'customer']);

        return [
            'country'   => 'PE',
            'type'      => $sale->document_type,           // boleta | factura
            'series'    => $sale->series,
            'number'    => $sale->number,
            'issued_at' => ($sale->created_at ?? now())->toIso8601String(),
            'currency'  => 'PEN',
            'issuer'    => $this->issuer(),
            'customer'  => $this->customer($sale),
            'lines'     => $this->lines($sale),
            'subtotal'  => (float) $sale->subtotal,         // base sin IGV
            'total'     => (float) $sale->total,            // con IGV
        ];
    }

    private function issuer(): array
    {
        return [
            'doc_type'   => 'tax_id',
            'doc_number' => (string) Setting::get('ruc', '20000000001'),
            'legal_name' => (string) Setting::get('business_name', 'MINIMARKET DEMO S.A.C.'),
            'address'    => Setting::get('address'),
        ];
    }

    private function customer(Sale $sale): array
    {
        $c = $sale->customer;

        // Factura requiere RUC; boleta admite consumidor final.
        if (!$c) {
            return ['doc_type' => 'sin_doc', 'doc_number' => '00000000', 'legal_name' => 'CLIENTE VARIOS'];
        }

        $docType = match ($c->doc_type) {
            'RUC'        => 'tax_id',
            'DNI'        => 'national_id',
            'CE', 'PAS'  => 'foreign_id',
            default      => $sale->document_type === 'factura' ? 'tax_id' : 'national_id',
        };

        return [
            'doc_type'   => $docType,
            'doc_number' => (string) ($c->doc_number ?: '00000000'),
            'legal_name' => (string) $c->name,
            'address'    => $c->address,
            'email'      => $c->email,
        ];
    }

    private function lines(Sale $sale): array
    {
        return $sale->items->map(function ($item) {
            $lineTotalWithIgv = (float) $item->subtotal;                 // con IGV
            $lineBase = round($lineTotalWithIgv / (1 + self::IGV), 2);   // sin IGV
            $unitBase = round(((float) $item->price) / (1 + self::IGV), 2);
            $igvAmount = round($lineTotalWithIgv - $lineBase, 2);

            return [
                'sku'         => (string) ($item->product?->barcode ?? $item->product_id),
                'description' => (string) $item->product_name,
                'quantity'    => (float) $item->quantity,
                'unit'        => 'NIU',
                'unit_price'  => $unitBase,   // sin IGV
                'line_total'  => $lineBase,   // sin IGV
                'taxes'       => [[
                    'kind'   => 'vat',
                    'rate'   => 18.0,
                    'amount' => $igvAmount,
                ]],
            ];
        })->all();
    }
}
