<?php

declare(strict_types=1);

namespace Facturacion\Domain\Port;

/**
 * Firma digital de documentos (puerto). Segun el pais la implementacion
 * usa firma XAdES-BES enveloped (PE/CO/CL) o sello CFDI (MX).
 */
interface XmlSigner
{
    public function sign(string $xml, string $certificatePem, string $privateKeyPem, ?string $passphrase = null): string;
}
