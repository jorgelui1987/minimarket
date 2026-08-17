<?php

declare(strict_types=1);

namespace Facturacion\Interface\Http;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Model\DocumentLine;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Model\DocumentType;
use Facturacion\Domain\Model\Money;
use Facturacion\Domain\Model\Party;
use Facturacion\Domain\Model\Tax;

/**
 * Convierte el JSON del ERP en el modelo CANONICO del dominio (anti-corruption
 * layer). Aqui se valida forma y se normaliza; los errores de forma lanzan
 * ValidationException antes de tocar cualquier autoridad.
 */
final class DocumentAssembler
{
    public function fromArray(array $p): CanonicalDocument
    {
        $currency = $p['currency'] ?? 'PEN';
        $lines = array_map(fn (array $l) => $this->line($l, $currency), $p['lines'] ?? []);

        return new CanonicalDocument(
            country:  CountryCode::from($p['country']),
            type:     DocumentType::from($p['type']),
            series:   (string) $p['series'],
            number:   (string) $p['number'],
            issuedAt: new \DateTimeImmutable($p['issued_at'] ?? 'now'),
            issuer:   $this->party($p['issuer']),
            customer: $this->party($p['customer']),
            lines:    $lines,
            subtotal: Money::of($p['subtotal'], $currency),
            taxTotals: $this->taxTotals($lines, $currency),
            total:    Money::of($p['total'], $currency),
            references: isset($p['reference']) ? $this->referenceFromArray($p['reference']) : null,
            annulmentReason: $p['annulment_reason'] ?? null,
            countryExtras: $p['country_extras'] ?? []
        );
    }

    private function line(array $l, string $currency): DocumentLine
    {
        $taxes = array_map(
            fn (array $t) => new Tax($t['kind'] ?? 'vat', (float) ($t['rate'] ?? 0), Money::of($t['amount'] ?? 0, $currency)),
            $l['taxes'] ?? []
        );

        return new DocumentLine(
            sku: (string) ($l['sku'] ?? ''),
            description: (string) ($l['description'] ?? ''),
            quantity: (float) ($l['quantity'] ?? 0),
            unit: (string) ($l['unit'] ?? 'NIU'),
            unitPrice: Money::of($l['unit_price'] ?? 0, $currency),
            lineTotal: Money::of($l['line_total'] ?? 0, $currency),
            taxes: $taxes
        );
    }

    /**
     * Agrega los impuestos de todas las lineas por tipo (kind).
     * @param DocumentLine[] $lines
     * @return Tax[]
     */
    private function taxTotals(array $lines, string $currency): array
    {
        $byKind = [];
        foreach ($lines as $line) {
            foreach ($line->taxes as $t) {
                $byKind[$t->kind] ??= ['rate' => $t->rate, 'amount' => 0.0];
                $byKind[$t->kind]['amount'] += $t->amount->toFloat();
            }
        }
        $totals = [];
        foreach ($byKind as $kind => $data) {
            $totals[] = new Tax($kind, $data['rate'], Money::of($data['amount'], $currency));
        }
        return $totals;
    }

    public function referenceFromArray(array $r): DocumentReference
    {
        return new DocumentReference(
            country: CountryCode::from($r['country']),
            type:    DocumentType::from($r['type']),
            series:  $r['series'],
            number:  $r['number'],
            externalId: $r['external_id'] ?? null
        );
    }

    private function party(array $x): Party
    {
        return new Party(
            docType: $x['doc_type'],
            docNumber: $x['doc_number'],
            legalName: $x['legal_name'],
            address: $x['address'] ?? null,
            email: $x['email'] ?? null,
            taxRegime: $x['tax_regime'] ?? null
        );
    }
}
