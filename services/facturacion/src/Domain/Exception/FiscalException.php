<?php

declare(strict_types=1);

namespace Facturacion\Domain\Exception;

/** Base de todas las excepciones del dominio de facturacion. */
abstract class FiscalException extends \RuntimeException
{
    /** Indica si la operacion puede reintentarse automaticamente. */
    abstract public function esReintentable(): bool;
}
