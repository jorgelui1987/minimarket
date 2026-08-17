<?php

declare(strict_types=1);

namespace Facturacion\Infrastructure\Sunat;

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\DocumentReference;
use Facturacion\Domain\Model\Party;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;

/**
 * Traduce el modelo CANONICO al modelo de Greenter. Aqui vive TODO el
 * conocimiento del catalogo SUNAT (tipos de documento, afectacion IGV,
 * leyendas). El dominio permanece agnostico.
 */
final class CanonicalToGreenterMapper
{
    private const IGV_RATE = 18.0;

    /** @param array<string,mixed> $config bloque 'PE' */
    public function __construct(
        private readonly array $config,
        private readonly NumeroALetras $numeroALetras = new NumeroALetras()
    ) {
    }

    // ---------------------------------------------------------------
    //  FACTURA / BOLETA
    // ---------------------------------------------------------------
    public function toInvoice(CanonicalDocument $d): Invoice
    {
        $tipoDoc = $d->type->value === 'boleta' ? '03' : '01';

        [$details, $totales] = $this->buildDetails($d);

        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101')          // Venta interna
            ->setTipoDoc($tipoDoc)
            ->setSerie($d->series)
            ->setCorrelativo($d->number)
            ->setFechaEmision($d->issuedAt)
            ->setFormaPago(new FormaPagoContado())
            ->setTipoMoneda($d->total->currency)
            ->setCompany($this->company())
            ->setClient($this->client($d->customer))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoOperExoneradas($totales['exoneradas'])
            ->setMtoOperInafectas($totales['inafectas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['igv'])
            ->setValorVenta($totales['valorVenta'])
            ->setSubTotal($totales['total'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends([$this->legendMontoLetras($totales['total'], $d->total->currency)]);

        return $invoice;
    }

    // ---------------------------------------------------------------
    //  NOTA DE CREDITO
    // ---------------------------------------------------------------
    public function toNote(CanonicalDocument $d): Note
    {
        [$details, $totales] = $this->buildDetails($d);
        $ref = $d->references;

        $note = (new Note())
            ->setUblVersion('2.1')
            ->setTipoDoc('07')                  // 07 = Nota de Credito
            ->setSerie($d->series)
            ->setCorrelativo($d->number)
            ->setFechaEmision($d->issuedAt)
            ->setTipDocAfectado($ref?->type->value === 'boleta' ? '03' : '01')
            ->setNumDocfectado($ref ? "{$ref->series}-{$ref->number}" : '')
            ->setCodMotivo($d->countryExtras['cod_motivo'] ?? '01') // 01 = Anulacion de la operacion
            ->setDesMotivo($d->annulmentReason ?? ($d->countryExtras['des_motivo'] ?? 'Anulacion de la operacion'))
            ->setTipoMoneda($d->total->currency)
            ->setCompany($this->company())
            ->setClient($this->client($d->customer))
            ->setMtoOperGravadas($totales['gravadas'])
            ->setMtoIGV($totales['igv'])
            ->setTotalImpuestos($totales['igv'])
            ->setMtoImpVenta($totales['total'])
            ->setDetails($details)
            ->setLegends([$this->legendMontoLetras($totales['total'], $d->total->currency)]);

        return $note;
    }

    // ---------------------------------------------------------------
    //  COMUNICACION DE BAJA (anulacion de factura)
    // ---------------------------------------------------------------
    public function toVoided(DocumentReference $ref, string $reason, string $correlativo, \DateTimeInterface $fecGeneracion): Voided
    {
        $detail = (new VoidedDetail())
            ->setTipoDoc($ref->type->value === 'boleta' ? '03' : '01')
            ->setSerie($ref->series)
            ->setCorrelativo($ref->number)
            ->setDesMotivoBaja($reason);

        return (new Voided())
            ->setCorrelativo($correlativo)               // 1..N por dia
            ->setFecGeneracion($fecGeneracion)           // fecha de emision del doc anulado
            ->setFecComunicacion(new \DateTime())        // hoy
            ->setCompany($this->company())
            ->setDetails([$detail]);
    }

    // ---------------------------------------------------------------
    //  helpers
    // ---------------------------------------------------------------

    /**
     * @return array{0: SaleDetail[], 1: array<string,float>}
     */
    private function buildDetails(CanonicalDocument $d): array
    {
        $details = [];
        $gravadas = $exoneradas = $inafectas = $igvTotal = 0.0;

        foreach ($d->lines as $line) {
            $base = $line->lineTotal->toFloat();            // sin IGV
            $kind = $line->taxes[0]->kind ?? 'vat';
            [$tipAfe, $igvLinea] = match ($kind) {
                'exempt'   => ['20', 0.0],                  // exonerado
                'inaffect' => ['30', 0.0],                  // inafecto
                default    => ['10', round($base * self::IGV_RATE / 100, 2)], // gravado
            };

            match ($tipAfe) {
                '20' => $exoneradas += $base,
                '30' => $inafectas += $base,
                default => $gravadas += $base,
            };
            $igvTotal += $igvLinea;

            $details[] = (new SaleDetail())
                ->setCodProducto($line->sku)
                ->setUnidad($line->unit ?: 'NIU')
                ->setDescripcion($line->description)
                ->setCantidad($line->quantity)
                ->setMtoValorUnitario($line->unitPrice->toFloat())
                ->setMtoValorVenta($base)
                ->setMtoBaseIgv($base)
                ->setPorcentajeIgv($tipAfe === '10' ? self::IGV_RATE : 0.0)
                ->setIgv($igvLinea)
                ->setTipAfeIgv($tipAfe)
                ->setTotalImpuestos($igvLinea)
                ->setMtoPrecioUnitario($this->precioUnitarioConIgv($line->unitPrice->toFloat(), $tipAfe));
        }

        $valorVenta = round($gravadas + $exoneradas + $inafectas, 2);
        $total = round($valorVenta + $igvTotal, 2);

        return [$details, [
            'gravadas'   => round($gravadas, 2),
            'exoneradas' => round($exoneradas, 2),
            'inafectas'  => round($inafectas, 2),
            'igv'        => round($igvTotal, 2),
            'valorVenta' => $valorVenta,
            'total'      => $total,
        ]];
    }

    private function precioUnitarioConIgv(float $unit, string $tipAfe): float
    {
        return $tipAfe === '10' ? round($unit * (1 + self::IGV_RATE / 100), 2) : round($unit, 2);
    }

    private function company(): Company
    {
        $address = (new Address())
            ->setUbigueo($this->config['ubigeo'] ?? '150101')
            ->setDepartamento($this->config['departamento'] ?? 'LIMA')
            ->setProvincia($this->config['provincia'] ?? 'LIMA')
            ->setDistrito($this->config['distrito'] ?? 'LIMA')
            ->setDireccion($this->config['direccion'] ?? '-');

        return (new Company())
            ->setRuc((string) $this->config['ruc'])
            ->setRazonSocial($this->config['razon_social'] ?? '')
            ->setNombreComercial($this->config['nombre_comercial'] ?? '')
            ->setAddress($address);
    }

    private function client(Party $p): Client
    {
        $tipoDoc = match ($p->docType) {
            'tax_id'     => '6', // RUC
            'national_id'=> '1', // DNI
            'foreign_id' => '7', // Pasaporte
            default      => '0', // sin documento (boleta consumidor final)
        };

        return (new Client())
            ->setTipoDoc($tipoDoc)
            ->setNumDoc($p->docNumber ?: '00000000')
            ->setRznSocial($p->legalName ?: 'CLIENTE VARIOS');
    }

    private function legendMontoLetras(float $total, string $moneda): Legend
    {
        return (new Legend())
            ->setCode('1000')
            ->setValue($this->numeroALetras->convertir($total, $moneda));
    }
}
