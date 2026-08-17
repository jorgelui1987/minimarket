<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Exception\ValidationException;
use Facturacion\Domain\Model\CanonicalDocument;

/**
 * Genera el TED (Timbre Electronico del Documento) que el SII exige en
 * cada DTE. Contiene la sintesis del documento firmada digitalmente por
 * el emisor, el CAF original (con su firma SII) y el QR.
 */
final class TedGenerator
{
    public function __construct(
        private readonly string $privateKeyPath,
        private readonly string $privateKeyPass = ''
    ) {
    }

    public function generate(CanonicalDocument $document, int $folio, string $cafXml, string $rutEmisor): string
    {
        $dteBuilder = new DteBuilder();
        $tipoDte    = $dteBuilder->tipoDte($document->type);

        $dd = implode('|', [
            $rutEmisor,
            (string) $tipoDte,
            (string) $folio,
            $document->issuedAt->format('Y-m-d'),
            $this->normalizeRut($document->customer->docNumber),
            $document->customer->legalName,
            (string) round($document->total->toFloat(), 0),
        ]);

        $firma = $this->sign($dd);
        if ($firma === '') {
            throw new ValidationException(['firma'], 'No se pudo firmar el TED.');
        }

        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $ted = $doc->createElement('TED');
        $ted->setAttribute('version', '1.0');
        $doc->appendChild($ted);

        $ddNode = $doc->createElement('DD');
        $ddNode->appendChild($doc->createElement('RE', $rutEmisor));
        $ddNode->appendChild($doc->createElement('TD', (string) $tipoDte));
        $ddNode->appendChild($doc->createElement('F', (string) $folio));
        $ddNode->appendChild($doc->createElement('FE', $document->issuedAt->format('Y-m-d')));
        $ddNode->appendChild($doc->createElement('RR', $this->normalizeRut($document->customer->docNumber)));
        $ddNode->appendChild($doc->createElement('RSR', $document->customer->legalName));
        $ddNode->appendChild($doc->createElement('MNT', (string) round($document->total->toFloat(), 0)));

        $cafBody = $this->extractCafBody($cafXml);
        if ($cafBody !== '') {
            $fragment = $doc->createDocumentFragment();
            $fragment->appendXML($cafBody);
            $ddNode->appendChild($fragment);
        }

        $ddNode->appendChild($doc->createElement('TSTED', $firma));

        $frmt = $doc->createElement('FRMT');
        $frmt->setAttribute('algoritmo', 'SHA256');
        $frmt->setAttribute('version', '1.0');
        $qr = $doc->createElement('QR');
        $qr->appendChild($doc->createTextNode($this->qrContent($rutEmisor, $tipoDte, $folio, $document)));
        $frmt->appendChild($qr);
        $ddNode->appendChild($frmt);

        $ted->appendChild($ddNode);

        return $doc->saveXML();
    }

    private function sign(string $data): string
    {
        $pkey = openssl_pkey_get_private(
            'file://' . $this->privateKeyPath,
            $this->privateKeyPass !== '' ? $this->privateKeyPass : null
        );
        if ($pkey === false) {
            return '';
        }

        $signature = '';
        if (!openssl_sign($data, $signature, $pkey, OPENSSL_ALGO_SHA256)) {
            return '';
        }
        return base64_encode($signature);
    }

    private function extractCafBody(string $cafXml): string
    {
        $doc = new \DOMDocument();
        if (!@$doc->loadXML($cafXml, LIBXML_NONET)) {
            return '';
        }
        $caf = $doc->getElementsByTagName('CAF')->item(0);
        if ($caf === null) {
            return '';
        }
        return $doc->saveXML($caf);
    }

    private function qrContent(string $rutEmisor, int $tipoDte, int $folio, CanonicalDocument $document): string
    {
        return json_encode([
            'rut'   => $rutEmisor,
            'td'    => (string) $tipoDte,
            'folio' => (string) $folio,
            'fecha' => $document->issuedAt->format('Y-m-d'),
            'monto' => (string) round($document->total->toFloat(), 0),
        ], JSON_UNESCAPED_UNICODE);
    }

    private function normalizeRut(string $rut): string
    {
        $rut = strtoupper(trim($rut));
        $rut = str_replace(['.', '-'], '', $rut);
        if ($rut === '') {
            return '66666666-6';
        }
        $dv   = substr($rut, -1);
        $body = substr($rut, 0, -1);
        if ($body === '') {
            return '66666666-6';
        }
        return $body . '-' . $dv;
    }
}