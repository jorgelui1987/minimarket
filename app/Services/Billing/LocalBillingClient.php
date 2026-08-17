<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Facturacion\Domain\Exception\BusinessRejectionException;
use Facturacion\Domain\Exception\FiscalException;
use Facturacion\Domain\Exception\ValidationException;
use Facturacion\Domain\Port\FiscalGateway;
use Facturacion\Domain\Result\EmissionResult;
use Facturacion\Infrastructure\Gateway\Peru\PeruSunatGateway;
use Facturacion\Infrastructure\Storage\FilesystemDocumentStorage;
use Facturacion\Infrastructure\Sunat\CanonicalToGreenterMapper;
use Facturacion\Infrastructure\Sunat\SeeFactory;
use Facturacion\Interface\Http\DocumentAssembler;

/**
 * Cliente de facturación IN-PROCESS: llama directamente al gateway del servicio
 * (services/facturacion) sin pasar por HTTP. Es la opción "junto al proyecto":
 * el ERP y el servicio comparten proceso, y Greenter hace el trabajo fiscal.
 *
 * Requiere: composer con autoload de Facturacion\ + greenter, y un certificado
 * PEM válido (ver USAGE_FASE1.md). Se activa con BILLING_DRIVER=local.
 */
final class LocalBillingClient implements BillingClient
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
            $r = $this->gateway()->anularFactura($ref, $reason);
            return new BillingResult(status: $r->status->value, externalId: $r->ticket ?? $r->externalId);
        });
    }

    public function estado(array $reference): BillingResult
    {
        return $this->guard(function () use ($reference) {
            $ref = $this->assembler->referenceFromArray($reference);
            $r = $this->gateway()->consultarEstado($ref);
            return new BillingResult(status: $r->status->value, externalId: $r->externalId, message: $r->authorityMessage);
        });
    }

    // ---------------------------------------------------------------

    private function gateway(): FiscalGateway
    {
        if ($this->gatewayCache instanceof FiscalGateway) {
            return $this->gatewayCache;
        }

        $config = BillingSettings::pe();

        return $this->gatewayCache = new PeruSunatGateway(
            new SeeFactory($config),
            new CanonicalToGreenterMapper($config),
            new FilesystemDocumentStorage((string) (config('facturacion.storage_path') ?: storage_path('facturacion'))),
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
