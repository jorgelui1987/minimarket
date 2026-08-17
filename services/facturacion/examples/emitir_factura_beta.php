<?php

declare(strict_types=1);

/**
 * Ejemplo ejecutable: emite una FACTURA de prueba contra el entorno BETA de
 * SUNAT usando el adaptador PeruSunatGateway (Fase 1).
 *
 *   1) composer install   (dentro de services/facturacion)
 *   2) Coloca el certificado demo en storage/facturacion/pe/certificate.pem
 *      (o ajusta la ruta abajo). En beta usa RUC 20000000001 / MODDATOS / MODDATOS.
 *   3) php examples/emitir_factura_beta.php
 *
 * NOTA: es un smoke-test. En la app real, el ERP llama al servicio por REST y
 * el use case EmitirFactura orquesta persistencia, auditoria e idempotencia.
 */

require __DIR__ . '/../vendor/autoload.php';

use Facturacion\Domain\Model\CanonicalDocument;
use Facturacion\Domain\Model\CountryCode;
use Facturacion\Domain\Model\DocumentLine;
use Facturacion\Domain\Model\DocumentType;
use Facturacion\Domain\Model\Money;
use Facturacion\Domain\Model\Party;
use Facturacion\Domain\Model\Tax;
use Facturacion\Domain\Port\AuditLogger;
use Facturacion\Infrastructure\Gateway\Peru\PeruSunatGateway;
use Facturacion\Infrastructure\Storage\FilesystemDocumentStorage;
use Facturacion\Infrastructure\Sunat\CanonicalToGreenterMapper;
use Facturacion\Infrastructure\Sunat\SeeFactory;

// --- configuracion del emisor (equivalente al bloque 'PE' de config) ---
$config = [
    'mode'             => 'beta',
    'endpoint_beta'    => 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService',
    'ruc'              => '20000000001',
    'clave_sol_user'   => 'MODDATOS',
    'clave_sol_pass'   => 'MODDATOS',
    'certificate_path' => __DIR__ . '/../storage/facturacion/pe/certificate.pem',
    'razon_social'     => 'MINIMARKET DEMO S.A.C.',
    'nombre_comercial' => 'Minimarket Demo',
    'ubigeo' => '150101', 'departamento' => 'LIMA', 'provincia' => 'LIMA',
    'distrito' => 'LIMA', 'direccion' => 'AV. PRINCIPAL 123',
];

// --- audit logger minimo para el ejemplo ---
$audit = new class implements AuditLogger {
    public function record(string $documentId, string $event, array $context = [], ?string $actor = null): void
    {
        echo sprintf("[audit] %-12s %s %s\n", $event, $documentId, json_encode($context));
    }
};

$gateway = new PeruSunatGateway(
    new SeeFactory($config),
    new CanonicalToGreenterMapper($config),
    new FilesystemDocumentStorage(__DIR__ . '/../storage/facturacion'),
    $audit,
    $config
);

// --- documento canonico de prueba: 1 item gravado con IGV 18% ---
$igv = new Tax('vat', 18.0, Money::of(18.00, 'PEN'));
$linea = new DocumentLine(
    sku: 'P001', description: 'Gaseosa 500ml', quantity: 1, unit: 'NIU',
    unitPrice: Money::of(100.00, 'PEN'), lineTotal: Money::of(100.00, 'PEN'), taxes: [$igv]
);

$doc = new CanonicalDocument(
    country: CountryCode::PE,
    type: DocumentType::FACTURA,
    series: 'F001',
    number: '1',
    issuedAt: new DateTimeImmutable(),
    issuer: new Party('tax_id', '20000000001', 'MINIMARKET DEMO S.A.C.'),
    customer: new Party('tax_id', '20000000001', 'EMPRESA CLIENTE S.A.C.'),
    lines: [$linea],
    subtotal: Money::of(100.00, 'PEN'),
    taxTotals: [$igv],
    total: Money::of(118.00, 'PEN')
);

try {
    $r = $gateway->emitirFactura($doc);
    echo "\n== RESULTADO ==\n";
    echo "Estado:   {$r->status->value}\n";
    echo "Externo:  {$r->externalId}\n";
    echo "XML:      {$r->xmlPath}\n";
    echo "CDR:      {$r->responsePath}\n";
    echo "Codigo:   {$r->authorityCode} - {$r->authorityMessage}\n";
    if ($r->observations) {
        echo "Obs:      " . implode('; ', $r->observations) . "\n";
    }
} catch (\Throwable $e) {
    echo "\n[ERROR] " . get_class($e) . ": " . $e->getMessage() . "\n";
}
