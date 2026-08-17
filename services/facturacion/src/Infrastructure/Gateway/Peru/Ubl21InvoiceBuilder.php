<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Gateway\Peru;

/**
 * @deprecated Fase 1: la construccion del XML UBL 2.1 y la firma XAdES ahora
 * las realiza Greenter (ver CanonicalToGreenterMapper + SeeFactory). Esta clase
 * queda como punto de extension por si se desea un builder UBL propio sin
 * dependencia externa. No se usa en el flujo actual.
 */
final class Ubl21InvoiceBuilder
{
}
