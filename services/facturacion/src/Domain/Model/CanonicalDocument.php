<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * ===================================================================
 *  MODELO CANONICO  (nucleo del dominio, agnostico de pais)
 * ===================================================================
 * El ERP y los use cases SOLO conocen esta forma. Cada Gateway de pais
 * es responsable de traducir este objeto al formato local (UBL 2.1,
 * DTE, CFDI 4.0, WSFEv1, ...). Asi, agregar un pais NO obliga a cambiar
 * ni el ERP ni el dominio: solo se agrega un adaptador nuevo.
 */
final class CanonicalDocument
{
    /**
     * @param DocumentLine[] $lines
     * @param Tax[]          $taxTotals
     * @param array<string,mixed> $countryExtras  Campos exclusivos de un pais que
     *        el ERP puede enviar sin contaminar el modelo (ej. AR condicionIVA,
     *        MX usoCFDI, CO tipoOperacion). El Gateway decide si los usa.
     */
    public function __construct(
        public readonly CountryCode  $country,
        public readonly DocumentType $type,
        public readonly string       $series,        // serie/prefijo (F001, A, etc.)
        public readonly string       $number,        // correlativo
        public readonly \DateTimeImmutable $issuedAt,
        public readonly Party        $issuer,
        public readonly Party        $customer,
        public readonly array        $lines,
        public readonly Money        $subtotal,
        public readonly array        $taxTotals,
        public readonly Money        $total,
        public readonly ?DocumentReference $references = null, // p/ notas de credito/debito
        public readonly ?string      $annulmentReason = null,
        public readonly array        $countryExtras = []
    ) {
    }

    public function fullId(): string
    {
        return sprintf('%s-%s-%s', $this->country->value, $this->series, $this->number);
    }
}
