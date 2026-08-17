<?php

declare(strict_types=1);

namespace Facturacion\Application;

use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Port\FiscalGateway;

/**
 * ===================================================================
 *  FACTORY / REGISTRO de estrategias por pais
 * ===================================================================
 * Resuelve el FiscalGateway correcto a partir del CountryCode.
 * Los gateways se registran (idealmente por el contenedor de DI leyendo
 * config/facturacion.php). Agregar un pais = registrar aqui su gateway,
 * sin tocar los use cases.
 */
final class FiscalGatewayFactory
{
    /** @var array<string, FiscalGateway|callable():FiscalGateway> */
    private array $registry = [];

    /**
     * @param iterable<FiscalGateway|callable():FiscalGateway> $gateways
     */
    public function __construct(iterable $gateways = [])
    {
        foreach ($gateways as $gateway) {
            $this->register($gateway);
        }
    }

    public function register(FiscalGateway|callable $gateway): void
    {
        if ($gateway instanceof FiscalGateway) {
            $this->registry[$gateway->country()->value] = $gateway;
            return;
        }
        // callable diferido (lazy): se resuelve la primera vez que se usa
        $this->registry[':deferred:' . spl_object_id((object) $gateway)] = $gateway;
    }

    public function for(CountryCode $country): FiscalGateway
    {
        $entry = $this->registry[$country->value] ?? null;

        if ($entry === null) {
            throw new \InvalidArgumentException(
                "No hay Gateway registrado para {$country->value} ({$country->nombreAutoridad()})."
            );
        }

        return $entry instanceof FiscalGateway ? $entry : $entry();
    }

    public function supports(CountryCode $country): bool
    {
        return isset($this->registry[$country->value]);
    }
}
