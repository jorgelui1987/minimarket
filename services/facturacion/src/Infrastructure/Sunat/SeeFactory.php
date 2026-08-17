<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Sunat;

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

/**
 * Construye y configura el cliente Greenter\See a partir de la config del pais.
 * Aisla en un solo lugar la carga del certificado, la Clave SOL y el endpoint,
 * de modo que el Gateway no conozca detalles de credenciales.
 */
final class SeeFactory
{
    /** @param array<string,mixed> $config bloque 'PE' de config/facturacion.php */
    public function __construct(private readonly array $config)
    {
    }

    public function create(): See
    {
        $see = new See();

        // Certificado PEM (certificado publico + llave privada concatenados).
        $pem = $this->loadCertificate();
        $see->setCertificate($pem);

        // Endpoint segun modo (beta/homologacion o produccion, o el OSE/PSE).
        $see->setService($this->resolveEndpoint());

        // Credenciales Clave SOL (usuario secundario recomendado).
        $see->setClaveSOL(
            (string) $this->config['ruc'],
            (string) $this->config['clave_sol_user'],
            (string) $this->config['clave_sol_pass']
        );

        return $see;
    }

    private function resolveEndpoint(): string
    {
        if (($this->config['mode'] ?? 'beta') === 'produccion') {
            return $this->config['endpoint_produccion'] ?? SunatEndpoints::FE_PRODUCCION;
        }
        return $this->config['endpoint_beta'] ?? SunatEndpoints::FE_BETA;
    }

    private function loadCertificate(): string
    {
        $path = (string) ($this->config['certificate_path'] ?? '');
        if ($path === '' || !is_file($path)) {
            throw new \RuntimeException(
                "Certificado SUNAT no encontrado en '{$path}'. " .
                'En beta puede usar el certificado demo (ver USAGE_FASE1.md).'
            );
        }
        $pem = file_get_contents($path);
        if ($pem === false || trim($pem) === '') {
            throw new \RuntimeException("No se pudo leer el certificado en '{$path}'.");
        }
        return $pem;
    }
}
