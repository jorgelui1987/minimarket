<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\DocumentType;

/**
 * Construye el XML DTE (Documento Tributario Electronico) en el formato
 * propio del SII de Chile. A diferencia de Peru (UBL 2.1), Chile usa un
 * esquema XML propio definido por el SII con estos nodos principales:
 *
 *   DTE (raiz, version 1.0)
 *   ├─ Documento
 *   │   ├─ Encabezado
 *   │   │   ├─ IdDoc   (tipo DTE, folio, fecha)
 *   │   │   ├─ Emisor  (RUT, razon social, direccion...)
 *   │   │   └─ Receptor (RUT, razon social, direccion...)
 *   │   ├─ Detalle   (lineas: NroLin, NmbItem, QtyItem, PrcItem...)
 *   │   └─ Totales   (MntNeto, IVA, MntTotal, TasaIVA...)
 *   └─ TED (Timbre Electronico, agregado al firmar)
 */
final class DteBuilder
{
    public function build(CanonicalDocument $document, int $folio, string $rutEmisor, array $emisor): string
    {
        $tipoDte = $this->tipoDte($document->type);

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $dte = $doc->createElement('DTE');
        $dte->setAttribute('version', '1.0');
        $dte->setAttribute('xmlns', 'http://www.sii.cl/SiiDte');
        $doc->appendChild($dte);

        // ==== Documento ====
        $documento = $doc->createElement('Documento');
        $dte->appendChild($documento);

        // -- Encabezado --
        $encabezado = $doc->createElement('Encabezado');
        $documento->appendChild($encabezado);

        $idDoc = $doc->createElement('IdDoc');
        $idDoc->appendChild($doc->createElement('TipoDTE', (string) $tipoDte));
        $idDoc->appendChild($doc->createElement('Folio', (string) $folio));
        $idDoc->appendChild($doc->createElement('FchEmis', $document->issuedAt->format('Y-m-d')));
        $idDoc->appendChild($doc->createElement('IndServicio', '3')); // boleta/factura venta
        $encabezado->appendChild($idDoc);

        // Emisor
        $emisorNode = $doc->createElement('Emisor');
        $emisorNode->appendChild($doc->createElement('RUTEmisor', $rutEmisor));
        $emisorNode->appendChild($doc->createElement('RznSocial', $emisor['razon_social'] ?? $document->issuer->legalName));
        $emisorNode->appendChild($doc->createElement('GiroEmis', $emisor['giro'] ?? 'Venta al por menor'));
        $emisorNode->appendChild($doc->createElement('DirOrigen', $emisor['direccion'] ?? $document->issuer->address ?? ''));
        $emisorNode->appendChild($doc->createElement('CmnaOrigen', $emisor['comuna'] ?? ''));
        $emisorNode->appendChild($doc->createElement('CiudadOrigen', $emisor['ciudad'] ?? ''));
        $encabezado->appendChild($emisorNode);

        // Receptor (opcional en boletas)
        $rutReceptor = $this->normalizeRut($document->customer->docNumber);
        $receptorNode = $doc->createElement('Receptor');
        $receptorNode->appendChild($doc->createElement('RUTRecep', $rutReceptor !== '' ? $rutReceptor : '66666666-6'));
        $receptorNode->appendChild($doc->createElement('RznSocialRecep', $document->customer->legalName !== '' ? $document->customer->legalName : 'Consumidor Final'));
        if ($document->customer->address !== null && $document->customer->address !== '') {
            $receptorNode->appendChild($doc->createElement('DirRecep', $document->customer->address));
        }
        $encabezado->appendChild($receptorNode);

        // -- Detalle (lineas) --
        foreach ($document->lines as $i => $line) {
            $detalle = $doc->createElement('Detalle');
            $detalle->appendChild($doc->createElement('NroLinDet', (string) ($i + 1)));
            $detalle->appendChild($doc->createElement('NmbItem', $line->description));
            if ($line->sku !== '') {
                $detalle->appendChild($doc->createElement('CdgoItem', $line->sku));
            }
            $detalle->appendChild($doc->createElement('QtyItem', $this->formatNumber($line->quantity, 4)));
            $detalle->appendChild($doc->createElement('UnmdMedida', $this->unidadSII($line->unit)));
            $detalle->appendChild($doc->createElement('PrcItem', $this->formatNumber($line->unitPrice->toFloat(), 4)));

            // Impuestos por linea (IVA 19%)
            foreach ($line->taxes as $tax) {
                if ($tax->kind === 'vat' && $tax->rate > 0) {
                    $cfdi = $doc->createElement('CFDIR', (string) round($tax->rate));
                    $detalle->appendChild($cfdi);
                }
            }

            $detalle->appendChild($doc->createElement('MontoItem', $this->formatNumber($line->lineTotal->toFloat(), 0)));
            $documento->appendChild($detalle);
        }

        // -- Totales --
        $totales = $doc->createElement('Totales');
        $totales->appendChild($doc->createElement('MntNeto', $this->formatNumber($document->subtotal->toFloat(), 0)));

        $iva = $this->getVatTotal($document);
        $totales->appendChild($doc->createElement('IVA', $this->formatNumber($iva, 0)));

        $totales->appendChild($doc->createElement('MntTotal', $this->formatNumber($document->total->toFloat(), 0)));
        $documento->appendChild($totales);

        return $doc->saveXML();
    }

    public function tipoDte(DocumentType $type): int
    {
        return match ($type) {
            DocumentType::FACTURA      => 33,
            DocumentType::BOLETA       => 39,
            DocumentType::NOTA_CREDITO => 61,
            DocumentType::NOTA_DEBITO  => 56,
        };
    }

    private function getVatTotal(CanonicalDocument $document): float
    {
        $iva = 0.0;
        foreach ($document->taxTotals as $tax) {
            if ($tax->kind === 'vat') {
                $iva += $tax->amount->toFloat();
            }
        }
        return $iva;
    }

    private function formatNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }

    private function unidadSII(string $unit): string
    {
        return match (strtoupper($unit)) {
            'KG', 'KGM'   => 'KGM',
            'LT', 'LTR'   => 'LTR',
            'M', 'MTR'    => 'MTR',
            'M2'          => 'MTK',
            'M3'          => 'MTQ',
            'HORA', 'HR'  => 'HR',
            'DIA'         => 'DAY',
            'SERVICIO'    => 'ZZ',
            default       => 'UN',
        };
    }

    private function normalizeRut(string $rut): string
    {
        $rut = strtoupper(trim($rut));
        $rut = str_replace(['.', '-'], '', $rut);
        if ($rut === '') {
            return '';
        }
        $dv   = substr($rut, -1);
        $body = substr($rut, 0, -1);
        if ($body === '') {
            return '';
        }
        return $body . '-' . $dv;
    }
}