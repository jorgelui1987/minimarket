<?php

declare(strict_types=1);

namespace Facturacion\Domain\Model;

/**
 * Codigo de pais ISO-3166 alpha-2 soportado por el servicio de facturacion.
 *
 * Agregar un pais nuevo = agregar un caso aqui y su Gateway en Infrastructure.
 * Ningun codigo existente se modifica (Open/Closed Principle).
 */
enum CountryCode: string
{
    case PE = 'PE'; // Peru  - SUNAT (UBL 2.1, OSE/PSE, CDR)
    case CO = 'CO'; // Colombia - DIAN (UBL 2.1, CUFE, validacion previa)
    case CL = 'CL'; // Chile - SII  (DTE propio, folios CAF)
    case AR = 'AR'; // Argentina - ARCA (SOAP WSFEv1, CAE)
    case MX = 'MX'; // Mexico - SAT  (CFDI 4.0, PAC, UUID)

    public function nombreAutoridad(): string
    {
        return match ($this) {
            self::PE => 'SUNAT',
            self::CO => 'DIAN',
            self::CL => 'SII',
            self::AR => 'ARCA',
            self::MX => 'SAT',
        };
    }

    /**
     * Modelo de autorizacion fiscal, condiciona el flujo del use case.
     * PRE  = la autoridad autoriza ANTES de entregar al cliente (CO, AR, MX).
     * POST = se emite y luego se declara/valida (PE post-emision via OSE/SUNAT).
     * SELF = el contribuyente auto-autoriza con folios entregados por la autoridad (CL/CAF).
     */
    public function modeloAutorizacion(): string
    {
        return match ($this) {
            self::CO, self::AR, self::MX => 'PRE',
            self::PE => 'POST',
            self::CL => 'SELF',
        };
    }
}
