<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Chile;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Exception\TransientTransmissionException;
use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Model\DocumentStatus;
use Facturacion\Domain\Model\DocumentType;
use Facturacion\Domain\Model\Money;
use Facturacion\Domain\Model\Party;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Domain\Port\DocumentStorage;
use Facturacion\Domain\Port\FiscalGateway;
use Facturacion\Domain\Port\XmlSigner;
use Facturacion\Domain\Result\AnnulmentResult;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Domain\Result\StatusResult;
use Facturacion\Infrastructure\Gateway\AbstractFiscalGateway;

/**
 * ===================================================================
 *  ADAPTADOR CHILE - SII  (Fase 4 - implementado)
 * ===================================================================
 * Modelo SELF-clearance: el contribuyente obtiene folios autorizados del
 * SII (CAF) y emite el DTE con el folio; luego lo envia (EnvioDTE) y
 * consulta el estado por TrackId.
 *
 * Flujo de emision:
 *   1. Reservar folio del CAF vigente (CafFolioManager->next()).
 *   2. Construir el XML DTE (DteBuilder) en formato propio del SII.
 *   3. Firmar digitalmente el DTE (XmlSigner).
 *   4. Generar el TED (Timbre Electronico) con la firma del emisor + CAF + QR.
 *   5. Persistir el XML firmado como artefacto legal.
 *   6. Enviar al SII (EnvioDTE) con reintentos ante fallos tecnicos.
 *   7. Capturar el TrackId y devolver estado ENVIANDO.
 */
final class ChileSiiGateway extends AbstractFiscalGateway implements FiscalGateway
{
    public function __construct(
        private readonly CafFolioManager $folios,
        private readonly DteBuilder $dteBuilder,
        private readonly TedGenerator $tedGenerator,
        private readonly XmlSigner $signer,
        private readonly SiiClient $client,
        private readonly DocumentStorage $storage,
        private readonly AuditLogger $audit,
        private readonly array $config
    ) {
    }

    public function country(): CountryCode
    {
        return CountryCode::CL;
    }

    public function emitirFactura(CanonicalDocument $document): EmissionResult
    {
        return $this->emitir($document);
    }

    public function emitirNotaCredito(CanonicalDocument $creditNote): EmissionResult
    {
        return $this->emitir($creditNote);
    }

    public function anularFactura(DocumentReference $reference, string $reason): AnnulmentResult
    {
        // En Chile NO se anula una factura: se emite una Nota de Credito (TipoDTE 61)
        // que referencia el documento original. Este metodo es un alias de conveniencia.
        $annulmentDoc = $this->buildAnnulmentDocument($reference, $reason);
        $result = $this->emitir($annulmentDoc);

        return new AnnulmentResult(
            status: $result->status,
            ticket: $result->externalId,
            externalId: $reference->number,
            responsePath: $result->responsePath,
            authorityMessage: $result->authorityMessage
        );
    }

    public function consultarEstado(DocumentReference $reference): StatusResult
    {
        $trackId = $reference->externalId;
        if ($trackId === null || $trackId === '') {
            return new StatusResult(DocumentStatus::PENDIENTE, null, 'No hay TrackId para consultar.');
        }

        try {
            $resp = $this->client->consultarEstado(
                $trackId,
                (string) ($this->config['rut_emisor'] ?? '')
            );
        } catch (TransientTransmissionException $e) {
            return new StatusResult(DocumentStatus::ERROR, $trackId, $e->getMessage());
        }

        $this->audit->record($reference->number, 'status_checked', [
            'trackid' => $trackId,
            'estado'  => $resp['estado'],
            'desc'    => $resp['descripcion'],
        ]);

        return new StatusResult(
            status: $this->mapEstado($resp['estado']),
            externalId: $trackId,
            authorityMessage: $resp['descripcion'],
            checkedAt: new \DateTimeImmutable()
        );
    }

    // ---------------------------------------------------------------
    //  nucleo comun de emision (factura y nota de credito)
    // ---------------------------------------------------------------
    private function emitir(CanonicalDocument $document): EmissionResult
    {
        $rutEmisor = (string) ($this->config['rut_emisor'] ?? '');
        if ($rutEmisor === '') {
            throw new BusinessRejectionException('CL', 'Falta rut_emisor en config/facturacion.php (CL).');
        }

        $observations = [];

        // 1) Reservar folio del CAF (bloquea si no hay CAF vigente)
        $folio = $this->folios->next($document->type, $observations);
        $this->audit->record($document->fullId(), 'folio_reserved', ['folio' => $folio]);

        // 2) Construir el XML DTE
        $dteXml = $this->dteBuilder->build($document, $folio, $rutEmisor, (array) ($this->config['emisor'] ?? []));

        // 3) Firmar digitalmente el DTE
        $cert = (string) ($this->config['certificate_path'] ?? '');
        if ($cert === '' || !is_file($cert)) {
            throw new BusinessRejectionException(
                'CL', "Falta el certificado del emisor en '{$cert}'. " .
                'Chile requiere firma electronica avanzada del contribuyente.'
            );
        }
        $pem = (string) file_get_contents($cert);
        $signedXml = $this->signer->sign(
            $dteXml,
            $pem,
            $pem,
            (string) ($this->config['certificate_pass'] ?? '')
        );

        // 4) Generar el TED con el CAF del folio reservado
        $cafXml = $this->loadCafForFolio($document->type, $folio);
        $tedXml = $this->tedGenerator->generate($document, $folio, $cafXml, $rutEmisor);

        // Agregar el TED al DTE firmado (el SII lo exige dentro del documento)
        $dteConTED = $this->appendTedToDte($signedXml, $tedXml);
        $signedXml = $dteConTED;

        // 5) Persistir el DTE firmado (artefacto legal)
        $name = $this->nombreDte($rutEmisor, $document, $folio);
        $xmlPath = $this->storage->put("CL/{$rutEmisor}/{$name}.xml", $signedXml, 'application/xml');
        $this->audit->record($document->fullId(), 'signed', ['xml' => $xmlPath]);

        // 6) Enviar al SII con reintentos ante fallos tecnicos
        $trackId = $this->sendWithXml($signedXml, $rutEmisor, $name);

        // 7) Persistir el TrackId como respuesta
        $respPath = $this->storage->put("CL/{$rutEmisor}/TRACK-{$name}.txt", $trackId, 'text/plain');

        return new EmissionResult(
            status: DocumentStatus::ENVIANDO,
            externalId: $trackId,
            xmlPath: $xmlPath,
            responsePath: $respPath,
            observations: $observations,
            authorityMessage: 'Enviado al SII. Pendiente de validacion (TrackId: ' . $trackId . ').'
        );
    }

    private function sendWithXml(string $signedXml, string $rutEmisor, string $name): string
    {
        return $this->withRetries(function (int $attempt) use ($signedXml, $rutEmisor, $name) {
            $this->audit->record($name, 'transmitted', ['attempt' => $attempt]);
            try {
                return $this->client->enviarDte([$signedXml], $rutEmisor);
            } catch (TransientTransmissionException $e) {
                throw $e;
            } catch (\Throwable $e) {
                throw new TransientTransmissionException('SII envio: ' . $e->getMessage(), 0, $e);
            }
        });
    }

    private function appendTedToDte(string $signedXml, string $tedXml): string
    {
        $doc = new \DOMDocument();
        if (!@$doc->loadXML($signedXml, LIBXML_NONET)) {
            throw new BusinessRejectionException('CL', 'DTE firmado no es XML valido para agregar TED.');
        }

        $fragment = $doc->createDocumentFragment();
        $ok = @$fragment->appendXML($tedXml);
        if ($ok) {
            $root = $doc->documentElement;
            if ($root !== null) {
                $root->appendChild($fragment);
            }
        }

        return $doc->saveXML();
    }

    private function loadCafForFolio(\Facturacion\Domain\Model\DocumentType $type, int $folio): string
    {
        $dir  = rtrim((string) ($this->config['caf_directory'] ?? ''), '/\\');
        if ($dir === '') {
            $dir = storage_path('facturacion/cl/caf');
        }
        $files = glob($dir . DIRECTORY_SEPARATOR . '*.xml') ?: [];
        foreach ($files as $file) {
            $xml = (string) file_get_contents($file);
            if (str_contains($xml, '<D>' . $folio . '</D>')) {
                return $xml;
            }
        }
        throw new BusinessRejectionException('CL', "No se encontro el CAF que contiene el folio {$folio}.");
    }

    private function buildAnnulmentDocument(DocumentReference $reference, string $reason): CanonicalDocument
    {
        // Construye una Nota de Credito minimo referenciando la factura original.
        $ref = new DocumentReference(
            country: CountryCode::CL,
            type: DocumentType::FACTURA,
            series: $reference->series,
            number: $reference->number,
            externalId: $reference->externalId
        );

        return new CanonicalDocument(
            country: CountryCode::CL,
            type: DocumentType::NOTA_CREDITO,
            series: 'NC',
            number: '0', // el folio real se asigna con el CAF
            issuedAt: new \DateTimeImmutable(),
            issuer: new Party('tax_id', $this->rutEmisor(), '', null, null, null),
            customer: new Party('tax_id', '66666666-6', 'Consumidor Final', null, null, null),
            lines: [],
            subtotal: new Money('0', 'CLP'),
            taxTotals: [],
            total: new Money('0', 'CLP'),
            references: $ref,
            annulmentReason: $reason
        );
    }

    private function rutEmisor(): string
    {
        return (string) ($this->config['rut_emisor'] ?? '');
    }

    private function mapEstado(string $estado): DocumentStatus
    {
        return match ($estado) {
            '0'  => DocumentStatus::ACEPTADO,
            '1'  => DocumentStatus::ACEPTADO, // aceptado con reparos
            '2', '3' => DocumentStatus::RECHAZADO,
            default => DocumentStatus::OBSERVADO,
        };
    }

    private function nombreDte(string $rutEmisor, CanonicalDocument $document, int $folio): string
    {
        $tipo = $this->dteBuilder->tipoDte($document->type);
        return sprintf('%s-T%s-F%s', $rutEmisor, $tipo, $folio);
    }
}
