<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Exception\FiscalException;
use Facturacion\Domain\Exception\ValidationException;
use Facturacion\Domain\Port\FiscalGateway;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Infrastructure\Gateway\Chile\CafFolioManager;
use Facturacion\Infrastructure\Gateway\Chile\ChileSiiGateway;
use Facturacion\Infrastructure\Gateway\Chile\DteBuilder;
use Facturacion\Infrastructure\Gateway\Chile\SiiClient;
use Facturacion\Infrastructure\Gateway\Chile\TedGenerator;
use Facturacion\Infrastructure\Gateway\Chile\XadesXmlSigner;
use Facturacion\Infrastructure\Storage\FilesystemDocumentStorage;
use Facturacion\Interface\Http\DocumentAssembler;

/**
 * Cliente de facturación IN-PROCESS para Chile (SII): llama directamente al
 * gateway del servicio (services/facturacion) sin pasar por HTTP.
 * Requiere certificado digital PEM y folios CAF descargados del SII.
 * Se activa con BILLING_DRIVER=local y fe_pais=CL.
 */
final class ChileBillingClient implements BillingClient
{
    private ?FiscalGateway $gatewayCache = null;

    public function __construct(
        private readonly DocumentAssembler $assembler = new DocumentAssembler()
    ) {
    }

    public function emitir(array $documentPayload, string $idempotencyKey): BillingResult
    {
        return $this->guard(function () use ($documentPayload) {
            $doc = $this->assembler->fromArray($documentPayload);
            return $this->toResult($this->gateway()->emitirFactura($doc));
        });
    }

    public function emitirNotaCredito(array $creditNotePayload, string $idempotencyKey): BillingResult
    {
        return $this->guard(function () use ($creditNotePayload) {
            $doc = $this->assembler->fromArray($creditNotePayload);
            return $this->toResult($this->gateway()->emitirNotaCredito($doc));
        });
    }

    public function anular(array $reference, string $reason): BillingResult
    {
        return $this->guard(function () use ($reference, $reason) {
            $ref = $this->assembler->referenceFromArray($reference);
            $r   = $this->gateway()->anularFactura($ref, $reason);
            return new BillingResult(status: $r->status->value, externalId: $r->ticket ?? $r->externalId);
        });
    }

    public function estado(array $reference): BillingResult
    {
        return $this->guard(function () use ($reference) {
            $ref = $this->assembler->referenceFromArray($reference);
            $r   = $this->gateway()->consultarEstado($ref);
            return new BillingResult(status: $r->status->value, externalId: $r->externalId, message: $r->authorityMessage);
        });
    }

    // ---------------------------------------------------------------

    private function gateway(): FiscalGateway
    {
        if ($this->gatewayCache instanceof FiscalGateway) {
            return $this->gatewayCache;
        }

        $cl = BillingSettings::cl();
        $storageRoot = (string) (config('facturacion.storage_path') ?: storage_path('facturacion'));
        $endpoint = (string) (config('facturacion.cl.endpoint') ?: 'https://palena.sii.cl/DTEWS/EnvioDTEService');
        $cafDir = (string) ($cl['caf_directory'] ?: storage_path('facturacion/cl/caf'));
        $certPath = (string) ($cl['certificate_path'] ?: storage_path('facturacion/cl/certificate.pem'));
        $certPass = (string) ($cl['certificate_pass'] ?? '');

        $config = [
            'rut_emisor'       => $cl['rut_emisor'],
            'certificate_path' => $certPath,
            'certificate_pass' => $certPass,
            'caf_directory'    => $cafDir,
            'emisor' => [
                'razon_social' => $cl['razon_social'],
                'giro'         => $cl['giro'],
                'direccion'    => $cl['direccion'],
                'comuna'       => $cl['comuna'],
                'ciudad'       => $cl['ciudad'],
            ],
        ];

        $folioManager = new CafFolioManager(
            $cafDir,
            storage_path('facturacion/cl/caf_state'),
            $cl['rut_emisor']
        );

        $soapFactory = function (string $ep): \SoapClient {
            return new \SoapClient($ep, [
                'stream_context' => stream_context_create([
                    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
                ]),
                'trace'          => true,
                'exceptions'     => true,
            ]);
        };

        return $this->gatewayCache = new ChileSiiGateway(
            $folioManager,
            new DteBuilder(),
            new TedGenerator($certPath, $certPass),
            new XadesXmlSigner(),
            new SiiClient($endpoint, $soapFactory),
            new FilesystemDocumentStorage($storageRoot),
            new LaravelAuditLogger(),
            $config
        );
    }

    private function toResult(EmissionResult $r): BillingResult
    {
        return new BillingResult(
            status: $r->status->value,
            externalId: $r->externalId,
            files: array_filter(['xml' => $r->xmlPath, 'cdr' => $r->responsePath, 'pdf' => $r->pdfPath]),
            observations: $r->observations,
            message: $r->authorityMessage
        );
    }

    /**
     * Traduce las excepciones fiscales a un BillingResult con estado adecuado
     * (rechazado/error) en vez de propagar, para que el ERP lo registre bien.
     */
    private function guard(callable $op): BillingResult
    {
        try {
            return $op();
        } catch (BusinessRejectionException | ValidationException $e) {
            return new BillingResult(status: 'rechazado', message: $e->getMessage());
        } catch (FiscalException $e) {
            // transitorio: reintentar más tarde
            return new BillingResult(status: 'error', message: $e->getMessage());
        }
    }
}