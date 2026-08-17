<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Reporting;

use Facturacion\Domain\Model\CanonicalDocument;

/**
 * Genera la REPRESENTACION IMPRESA (HTML) del comprobante, lista para
 * imprimir o convertir a PDF. Es dependency-light: para PDF en produccion
 * basta pasar este HTML por dompdf o por greenter/report.
 */
final class HtmlInvoiceRenderer
{
    /** @param array<string,mixed> $emisor datos del emisor (razon social, ruc, direccion) */
    public function __construct(private readonly array $emisor)
    {
    }

    public function render(CanonicalDocument $d, ?string $qrData = null): string
    {
        $tipo = match ($d->type->value) {
            'boleta' => 'BOLETA DE VENTA ELECTRONICA',
            'nota_credito' => 'NOTA DE CREDITO ELECTRONICA',
            'nota_debito' => 'NOTA DE DEBITO ELECTRONICA',
            default => 'FACTURA ELECTRONICA',
        };
        $ruc = htmlspecialchars((string) ($this->emisor['ruc'] ?? ''));
        $razon = htmlspecialchars((string) ($this->emisor['razon_social'] ?? ''));
        $dir = htmlspecialchars((string) ($this->emisor['direccion'] ?? ''));
        $serieNum = htmlspecialchars($d->series . '-' . $d->number);
        $cliente = htmlspecialchars($d->customer->legalName);
        $cliDoc = htmlspecialchars($d->customer->docNumber);
        $fecha = $d->issuedAt->format('d/m/Y');
        $moneda = htmlspecialchars($d->total->currency);
        $total = htmlspecialchars($d->total->amount);

        $rows = '';
        foreach ($d->lines as $l) {
            $rows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td class="r">%s</td><td class="r">%s</td><td class="r">%s</td></tr>',
                htmlspecialchars($l->sku),
                htmlspecialchars($l->description),
                rtrim(rtrim(number_format($l->quantity, 2), '0'), '.'),
                htmlspecialchars($l->unitPrice->amount),
                htmlspecialchars($l->lineTotal->amount)
            );
        }

        $qr = $qrData ? '<div class="qr">' . htmlspecialchars($qrData) . '</div>' : '';

        return <<<HTML
        <!doctype html><html lang="es"><head><meta charset="utf-8">
        <style>
          body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#111;margin:24px}
          .box{border:1px solid #10b981;border-radius:8px;padding:10px 14px;text-align:center;float:right;width:230px}
          .box .t{font-weight:bold;color:#064e3b}
          h1{font-size:16px;margin:0 0 2px}
          table{width:100%;border-collapse:collapse;margin-top:16px;clear:both}
          th{background:#10b981;color:#fff;padding:6px;text-align:left;font-size:11px}
          td{border-bottom:1px solid #e5e7eb;padding:6px}
          .r{text-align:right}
          .tot{margin-top:12px;text-align:right;font-size:14px;font-weight:bold;color:#064e3b}
          .qr{margin-top:14px;font-family:monospace;font-size:10px;color:#555}
        </style></head><body>
          <div class="box"><div class="t">R.U.C. {$ruc}</div><div class="t">{$tipo}</div><div class="t">{$serieNum}</div></div>
          <h1>{$razon}</h1><div>{$dir}</div>
          <div style="margin-top:10px">Fecha de emision: {$fecha}</div>
          <div>Cliente: {$cliente} &nbsp; Doc: {$cliDoc}</div>
          <table><thead><tr><th>Codigo</th><th>Descripcion</th><th class="r">Cant.</th><th class="r">V.Unit</th><th class="r">Importe</th></tr></thead>
          <tbody>{$rows}</tbody></table>
          <div class="tot">TOTAL {$moneda} {$total}</div>
          {$qr}
        </body></html>
        HTML;
    }
}
