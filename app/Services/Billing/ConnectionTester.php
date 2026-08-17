<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Illuminate\Support\Facades\Http;

/**
 * Verifica la configuración de facturación sin emitir un comprobante real:
 * certificado (existe, es PEM válido, vigencia), credenciales completas y
 * alcance del endpoint (WSDL de SUNAT o salud del servicio REST).
 */
final class ConnectionTester
{
    /** @return array{overall:string, checks:array<int,array{label:string,status:string,detail:string}>} */
    public function run(): array
    {
        return match (BillingSettings::driver()) {
            'local' => $this->finalize($this->testLocal()),
            'rest'  => $this->finalize($this->testRest()),
            default => [
                'overall' => 'warn',
                'checks'  => [$this->check('Driver de emisión', 'warn', 'Está en "Ninguno": las ventas no se envían a SUNAT.')],
            ],
        };
    }

    /** @return array<int,array{label:string,status:string,detail:string}> */
    private function testLocal(): array
    {
        $pe = BillingSettings::pe();
        $checks = [];

        // 1) RUC
        $ruc = (string) $pe['ruc'];
        $checks[] = preg_match('/^\d{11}$/', $ruc)
            ? $this->check('RUC', 'ok', $ruc)
            : $this->check('RUC', 'error', 'Debe tener 11 dígitos.');

        // 2) Clave SOL
        $checks[] = ($pe['clave_sol_user'] && $pe['clave_sol_pass'])
            ? $this->check('Clave SOL', 'ok', 'Usuario y clave configurados.')
            : $this->check('Clave SOL', 'error', 'Falta usuario o clave SOL.');

        // 3) Certificado
        $checks[] = $this->checkCertificate((string) $pe['certificate_path']);

        // 4) Endpoint (WSDL)
        $endpoint = ($pe['mode'] === 'produccion' ? $pe['endpoint_produccion'] : $pe['endpoint_beta'])
            ?? 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
        $checks[] = $this->checkEndpoint($endpoint . '?wsdl', 'Endpoint SUNAT (' . $pe['mode'] . ')');

        return $checks;
    }

    /** @return array<int,array{label:string,status:string,detail:string}> */
    private function testRest(): array
    {
        $url = BillingSettings::restUrl();
        if ($url === '') {
            return [$this->check('Servicio REST', 'error', 'Falta la URL del servicio.')];
        }

        $checks = [];
        try {
            $res = Http::withToken(BillingSettings::restToken())->timeout(8)->get($url);
            $checks[] = $res->status() < 500
                ? $this->check('Servicio REST', 'ok', 'Responde (HTTP ' . $res->status() . ').')
                : $this->check('Servicio REST', 'error', 'Error del servicio (HTTP ' . $res->status() . ').');
        } catch (\Throwable $e) {
            $checks[] = $this->check('Servicio REST', 'error', 'No se pudo conectar: ' . $e->getMessage());
        }

        return $checks;
    }

    private function checkCertificate(string $path): array
    {
        if ($path === '' || !is_file($path)) {
            return $this->check('Certificado', 'error', 'No se encontró el archivo en la ruta indicada.');
        }
        $pem = @file_get_contents($path);
        if ($pem === false || trim($pem) === '') {
            return $this->check('Certificado', 'error', 'El archivo está vacío o no se puede leer.');
        }

        $cert = @openssl_x509_parse($pem);
        if (!is_array($cert)) {
            return $this->check('Certificado', 'error', 'El archivo no es un certificado PEM válido.');
        }

        $validTo = $cert['validTo_time_t'] ?? null;
        if ($validTo && $validTo < time()) {
            return $this->check('Certificado', 'error', 'Vencido el ' . date('d/m/Y', $validTo) . '.');
        }

        $cn = $cert['subject']['CN'] ?? 'certificado';
        $venc = $validTo ? ' · vence ' . date('d/m/Y', $validTo) : '';
        return $this->check('Certificado', 'ok', $cn . $venc . '.');
    }

    private function checkEndpoint(string $url, string $label): array
    {
        try {
            $res = Http::timeout(8)->get($url);
            return $res->status() < 500
                ? $this->check($label, 'ok', 'Alcanzable (HTTP ' . $res->status() . ').')
                : $this->check($label, 'error', 'Respuesta HTTP ' . $res->status() . '.');
        } catch (\Throwable $e) {
            return $this->check($label, 'error', 'Sin conexión: ' . $e->getMessage());
        }
    }

    private function check(string $label, string $status, string $detail): array
    {
        return ['label' => $label, 'status' => $status, 'detail' => $detail];
    }

    /** @param array<int,array{label:string,status:string,detail:string}> $checks */
    private function finalize(array $checks): array
    {
        $statuses = array_column($checks, 'status');
        $overall = in_array('error', $statuses, true) ? 'error' : (in_array('warn', $statuses, true) ? 'warn' : 'ok');

        return ['overall' => $overall, 'checks' => $checks];
    }
}
