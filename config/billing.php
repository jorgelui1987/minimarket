<?php

/**
 * Configuracion del cliente del ERP hacia el servicio de facturacion.
 * El ERP solo necesita saber la URL y el token; nada de logica fiscal.
 */
return [
    'base_url' => env('BILLING_SERVICE_URL', 'http://localhost:8090'),
    'token'    => env('BILLING_SERVICE_TOKEN', ''),

    // Si se desea desactivar la facturacion electronica y seguir con ticket interno.
    'enabled'  => env('BILLING_ENABLED', false),

    // Driver de emision:
    //   'null'  -> no emite (deja 'pendiente'). Default seguro.
    //   'local' -> in-process con Greenter (LocalBillingClient). Requiere cert.
    //   'rest'  -> servicio HTTP externo (RestBillingClient).
    'driver'   => env('BILLING_DRIVER', 'null'),

    // Pais fiscal por defecto del emisor.
    'default_country' => env('BILLING_COUNTRY', 'PE'),

    // Emitir automaticamente al cerrar la venta en el POS (boleta/factura).
    'auto_emit' => env('BILLING_AUTO_EMIT', true),

    // Raiz de almacenamiento de XML/CDR del servicio de facturacion, para que el
    // ERP pueda ofrecer la descarga cuando comparten disco.
    'storage_path' => env('BILLING_STORAGE_PATH', base_path('services/facturacion/storage/facturacion')),
];
