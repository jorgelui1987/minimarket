<?php

/**
 * Configuración fiscal del emisor (Perú/SUNAT) usada por el driver 'local'
 * de facturación (LocalBillingClient → PeruSunatGateway con Greenter).
 * En 'beta' se usa el RUC/clave demo de SUNAT y el certificado demo.
 */
return [
    'pe' => [
        'mode'             => env('PE_MODE', 'beta'), // beta | produccion

        'endpoint_beta'       => env('PE_ENDPOINT_BETA', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
        'endpoint_produccion' => env('PE_ENDPOINT_PROD', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'),

        'ruc'            => env('PE_RUC', '20000000001'),
        'clave_sol_user' => env('PE_SOL_USER', 'MODDATOS'),
        'clave_sol_pass' => env('PE_SOL_PASS', 'MODDATOS'),

        'certificate_path' => env('PE_CERT_PATH', storage_path('facturacion/pe/certificate.pem')),

        'razon_social'     => env('PE_RAZON_SOCIAL', 'MINIMARKET DEMO S.A.C.'),
        'nombre_comercial' => env('PE_NOMBRE_COMERCIAL', 'Minimarket Demo'),
        'ubigeo'           => env('PE_UBIGEO', '150101'),
        'departamento'     => env('PE_DEPARTAMENTO', 'LIMA'),
        'provincia'        => env('PE_PROVINCIA', 'LIMA'),
        'distrito'         => env('PE_DISTRITO', 'LIMA'),
        'direccion'        => env('PE_DIRECCION', 'Av. Principal 123'),
    ],

    'cl' => [
        // Endpoint del WebService de EnvioDTE del SII (produccion).
        'endpoint' => env('CL_SII_ENDPOINT', 'https://palena.sii.cl/DTEWS/EnvioDTEService'),

        // RUT del emisor (formato 76123456-7).
        'rut_emisor' => env('CL_RUT_EMISOR', '76123456-7'),

        // Certificado digital (firma electronica avanzada) en formato PEM.
        'certificate_path' => env('CL_CERT_PATH', storage_path('facturacion/cl/certificate.pem')),
        'certificate_pass' => env('CL_CERT_PASS', ''),

        // Directorio donde se guardan los CAF (.xml) descargados del SII.
        'caf_directory' => env('CL_CAF_DIR', storage_path('facturacion/cl/caf')),

        // Datos de domicilio fiscal del emisor (para el DTE).
        'emisor' => [
            'razon_social' => env('CL_RAZON_SOCIAL', 'MINIMARKET DEMO LTDA'),
            'giro'         => env('CL_GIRO', 'Venta al por menor en comercios no especializados'),
            'direccion'    => env('CL_DIRECCION', 'Av. Principal 123'),
            'comuna'       => env('CL_COMUNA', 'Santiago'),
            'ciudad'       => env('CL_CIUDAD', 'Santiago'),
        ],
    ],

    // Dónde persiste el servicio los XML/CDR (mismo disco que usa la descarga).
    'storage_path' => storage_path('facturacion'),
];
