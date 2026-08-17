<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Storage;

use Facturacion\Domain\Port\DocumentStorage;

/**
 * Implementacion de DocumentStorage sobre el sistema de archivos local.
 * Guarda XML firmado, CDR y PDF bajo una raiz configurable. En produccion
 * puede sustituirse por un adaptador S3 sin tocar el resto del sistema.
 */
final class FilesystemDocumentStorage implements DocumentStorage
{
    public function __construct(
        private readonly string $root,      // ej. storage_path('facturacion')
        private readonly string $baseUrl = '' // opcional, para exponer URLs
    ) {
    }

    public function put(string $relativePath, string $contents, string $contentType): string
    {
        $full = $this->absolute($relativePath);
        $dir = \dirname($full);
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException("No se pudo crear el directorio: {$dir}");
        }
        if (file_put_contents($full, $contents) === false) {
            throw new \RuntimeException("No se pudo escribir el archivo: {$full}");
        }
        return $relativePath;
    }

    public function get(string $path): string
    {
        $full = $this->absolute($path);
        $data = is_file($full) ? file_get_contents($full) : false;
        if ($data === false) {
            throw new \RuntimeException("Archivo no encontrado: {$path}");
        }
        return $data;
    }

    public function url(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function absolute(string $relativePath): string
    {
        return rtrim($this->root, '/\\') . DIRECTORY_SEPARATOR . ltrim($relativePath, '/\\');
    }
}
