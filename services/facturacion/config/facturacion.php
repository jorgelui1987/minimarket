<?php

declare(strict_types=1);

/**
 * ===================================================================
 *  REGISTRO DE PAISES  (punto unico de extension)
 * ===================================================================
 * Para ACTIVAR un pais nuevo: implementar su Gateway y agregar su entrada
 * aqui. El contenedor de DI construye cada Gateway y lo registra en el
 * FiscalGatewayFactory. NINGUN otro archivo se modifica.
 *
 * 'enabled' permite activar/desactivar un pais por despliegue (feature flag).
 */

use Facturacion\Domain\Model\CountryCode;

return [

    'default_country' => CountryCode::PE->value,

    'countries' => [

        CountryCode::PE->value => [
            'enabled'  => true,   // Fase 1 - operativo (Greenter)
            'gateway'  => \Facturacion\Infrastructure\Gateway\Peru\PeruSunatGateway::class,

            // Entorno: 'beta' (homologacion SUNAT) o 'produccion'.
            'mode'     => env('PE_MODE', 'beta'),

            // Endpoints. Si se usa un OSE/PSE, reemplazar por su URL.
            // Por defecto apunta al servicio de SUNAT segun el modo.
            'endpoint_beta'       => env('PE_ENDPOINT_BETA', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
            'endpoint_produccion' => env('PE_ENDPOINT_PROD', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),

            // Datos del emisor y Clave SOL (usuario secundario recomendado).
            'ruc'          => env('PE_RUC', '20000000001'),
            'clave_sol_user' => env('PE_SOL_USER', 'MODDATOS'),   // en beta: MODDATOS
            'clave_sol_pass' => env('PE_SOL_PASS', 'MODDATOS'),   // en beta: MODDATOS

            // Certificado digital en formato PEM (cert + llave concatenados).
            // En beta se usa el certificado demo de SUNAT/Greenter.
            'certificate_path' => env('PE_CERT_PATH', storage_path('facturacion/pe/certificate.pem')),

            // Datos de domicilio fiscal del emisor (para la Company de Greenter).
            'razon_social'     => env('PE_RAZON_SOCIAL', 'MINIMARKET DEMO S.A.C.'),
            'nombre_comercial' => env('PE_NOMBRE_COMERCIAL', 'Minimarket Demo'),
            'ubigeo'           => env('PE_UBIGEO', '150101'),
            'departamento'     => env('PE_DEPARTAMENTO', 'LIMA'),
            'provincia'        => env('PE_PROVINCIA', 'LIMA'),
            'distrito'         => env('PE_DISTRITO', 'LIMA'),
            'direccion'        => env('PE_DIRECCION', 'Av. Principal 123'),
        ],

        CountryCode::CO->value => [
            'enabled'  => false,  // Fase 3
            'gateway'  => \Facturacion\Infrastructure\Gateway\Colombia\ColombiaDianGateway::class,
            'endpoint' => env('CO_DIAN_ENDPOINT'),
        ],

        CountryCode::CL->value => [
            'enabled'  => false,  // Fase 4
            'gateway'  => \Facturacion\Infrastructure\Gateway\Chile\ChileSiiGateway::class,

            // Endpoint del WebService de EnvioDTE del SII (produccion).
            'endpoint' => env('CL_SII_ENDPOINT', 'https://palena.sii.cl/DTEWS/EnvioDTEService'),

            // RUT del emisor (formato 76123456-7).
            'rut_emisor'   => env('CL_RUT_EMISOR', '76123456-7'),

            // Certificado digital (firma electronica avanzada) en formato PEM.
            'certificate_path' => env('CL_CERT_PATH', storage_path('facturacion/cl/certificate.pem')),
            'certificate_pass' => env('CL_CERT_PASS', ''),

            // Directorio donde se guardan los CAF (.xml) descargados del SII.
            'caf_directory'    => env('CL_CAF_DIR', storage_path('facturacion/cl/caf')),

            // Datos de domicilio fiscal del emisor (para el DTE).
            'emisor' => [
                'razon_social' => env('CL_RAZON_SOCIAL', 'MINIMARKET DEMO LTDA'),
                'giro'         => env('CL_GIRO', 'Venta al por menor en comercios no especializados'),
                'direccion'    => env('CL_DIRECCION', 'Av. Principal 123'),
                'comuna'       => env('CL_COMUNA', 'Santiago'),
                'ciudad'       => env('CL_CIUDAD', 'Santiago'),
            ],
        ],

        CountryCode::AR->value => [
            'enabled'  => false,  // Fase 5
            'gateway'  => \Facturacion\Infrastructure\Gateway\Argentina\ArgentinaArcaGateway::class,
            'endpoint' => env('AR_ARCA_WSFE_ENDPOINT'),
        ],

        CountryCode::MX->value => [
            'enabled'  => false,  // Fase 6
            'gateway'  => \Facturacion\Infrastructure\Gateway\Mexico\MexicoSatGateway::class,
            'pac'      => env('MX_PAC_PROVIDER'),
            'endpoint' => env('MX_PAC_ENDPOINT'),
        ],
    ],

    'retries' => [
        'max_attempts'  => 4,
        'base_delay_ms' => 500,
    ],

    'storage' => [
        'disk'           => env('FACTURACION_DISK', 'facturacion'),
        'retention_years' => 6, // >= plazo legal mas exigente de los paises soportados
    ],
];
