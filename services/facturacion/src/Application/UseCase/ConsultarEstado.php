<?php

declare(strict_types=1);

namespace Facturacion\Application\UseCase;

use Facturacion\Application\FiscalGatewayFactory;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Result\StatusResult;

/**
 * CASO DE USO: Consultar el estado de un comprobante ante la autoridad.
 * Util para reconciliar emisiones asincronas (PE ticket, CL trackid, etc.).
 */
final class ConsultarEstado
{
    public function __construct(private readonly FiscalGatewayFactory $gateways)
    {
    }

    public function handle(DocumentReference $reference): StatusResult
    {
        return $this->gateways->for($reference->country)->consultarEstado($reference);
    }
}
