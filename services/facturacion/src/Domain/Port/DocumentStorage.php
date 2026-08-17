<?php

declare(strict_types=1);

namespace Facturacion\Domain\Port;

/**
 * Almacenamiento de artefactos legales (XML firmado, CDR/acuse, PDF).
 * La implementacion puede ser filesystem local, S3, etc.
 * Debe garantizar retencion e inmutabilidad (WORM) por el plazo legal.
 */
interface DocumentStorage
{
    /** Guarda contenido y devuelve la ruta/URI persistente. */
    public function put(string $relativePath, string $contents, string $contentType): string;

    public function get(string $path): string;

    public function url(string $path): string;
}
