<?php

declare(strict_types=1);

namespace Facturacion\Application\UseCase;

use Facturacion\Application\FiscalGatewayFactory;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Domain\Result\AnnulmentResult;

/**
 * CASO DE USO: Anular / dar de baja un comprobante ya emitido.
 * PE -> Comunicacion de Baja (ticket); MX -> cancelacion con acuse;
 * AR -> nota de credito equivalente; CO/CL -> nota de credito.
 * El Gateway encapsula la mecanica local.
 */
final class AnularFactura
{
    public function __construct(
        private readonly FiscalGatewayFactory $gateways,
        private readonly AuditLogger $audit
    ) {
    }

    public function handle(DocumentReference $reference, string $reason): AnnulmentResult
    {
        $gateway = $this->gateways->for($reference->country);
        $result = $gateway->anularFactura($reference, $reason);

        $this->audit->record($reference->number, 'annulment', [
            'country' => $reference->country->value,
            'reason'  => $reason,
            'status'  => $result->status->value,
            'ticket'  => $result->ticket,
        ]);

        return $result;
    }
}
