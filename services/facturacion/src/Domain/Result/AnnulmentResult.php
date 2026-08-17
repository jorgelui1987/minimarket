<?php

declare(strict_types=1);

namespace Facturacion\Domain\Result;

use Facturacion\Domain\Model\DocumentStatus;

final class AnnulmentResult
{
    public function __construct(
        public readonly DocumentStatus $status,
        public readonly ?string $ticket = null,          // PE: ticket de comunicacion de baja
        public readonly ?string $externalId = null,
        public readonly ?string $responsePath = null,
        public readonly ?string $authorityMessage = null
    ) {
    }
}
