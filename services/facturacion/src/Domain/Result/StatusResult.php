<?php

declare(strict_types=1);

namespace Facturacion\Domain\Result;

use Facturacion\Domain\Model\DocumentStatus;

final class StatusResult
{
    public function __construct(
        public readonly DocumentStatus $status,
        public readonly ?string $externalId = null,
        public readonly ?string $authorityMessage = null,
        public readonly ?\DateTimeImmutable $checkedAt = null
    ) {
    }
}
