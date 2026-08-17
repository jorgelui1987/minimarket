<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;

/**
 * Implementacion REST del BillingClient. Habla con el servicio de facturacion
 * (services/facturacion) por HTTP. El ERP NO conoce SUNAT/DIAN/SII/ARCA/SAT.
 *
 * Registrar en un ServiceProvider:
 *   $this->app->bind(BillingClient::class, RestBillingClient::class);
 */
final class RestBillingClient implements BillingClient
{
    public function __construct(
        private readonly string $baseUrl,   // config('billing.base_url')
        private readonly string $apiToken   // config('billing.token')
    ) {
    }

    public function emitir(array $documentPayload, string $idempotencyKey): BillingResult
    {
        $res = Http::withToken($this->apiToken)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->timeout(15)
            ->retry(2, 300) // reintento de transporte; el servicio maneja el reintento fiscal
            ->post("{$this->baseUrl}/api/v1/documents", $documentPayload)
            ->throw()
            ->json();

        return new BillingResult(
            status: $res['status'],
            externalId: $res['external_id'] ?? null,
            files: $res['files'] ?? [],
            observations: $res['observations'] ?? []
        );
    }

    public function emitirNotaCredito(array $payload, string $idempotencyKey): BillingResult
    {
        $res = Http::withToken($this->apiToken)
            ->withHeaders(['Idempotency-Key' => $idempotencyKey])
            ->post("{$this->baseUrl}/api/v1/documents/credit-note", $payload)
            ->throw()->json();

        return new BillingResult($res['status'], $res['external_id'] ?? null);
    }

    public function anular(array $reference, string $reason): BillingResult
    {
        $res = Http::withToken($this->apiToken)
            ->post("{$this->baseUrl}/api/v1/documents/void", ['reference' => $reference, 'reason' => $reason])
            ->throw()->json();

        return new BillingResult($res['status'], $res['ticket'] ?? null);
    }

    public function estado(array $reference): BillingResult
    {
        $res = Http::withToken($this->apiToken)
            ->get("{$this->baseUrl}/api/v1/documents", $reference)
            ->throw()->json();

        return new BillingResult($res['status'], $res['external_id'] ?? null, message: $res['message'] ?? null);
    }
}
