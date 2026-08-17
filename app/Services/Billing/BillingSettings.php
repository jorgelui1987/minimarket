<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\Setting;

/**
 * Resuelve la configuración de facturación desde la tabla settings (por empresa),
 * con fallback a config/env. Esto permite administrar la facturación desde la UI
 * (módulo "Facturación Electrónica") sin tocar el .env.
 */
final class BillingSettings
{
    public static function enabled(): bool
    {
        return (bool) (int) Setting::get('fe_enabled', config('billing.enabled') ? '1' : '0');
    }

    public static function driver(): string
    {
        return (string) Setting::get('fe_driver', config('billing.driver', 'null'));
    }

    public static function autoEmit(): bool
    {
        return (bool) (int) Setting::get('fe_auto_emit', config('billing.auto_emit', true) ? '1' : '0');
    }

    public static function restUrl(): string
    {
        return (string) Setting::get('fe_rest_url', config('billing.base_url', ''));
    }

    public static function restToken(): string
    {
        return (string) Setting::get('fe_rest_token', config('billing.token', ''));
    }

    /** Configuración del emisor PE para el gateway SUNAT (Greenter). */
    public static function pe(): array
    {
        $c = (array) config('facturacion.pe');

        return [
            'mode'                => Setting::get('fe_mode', $c['mode'] ?? 'beta'),
            'endpoint_beta'       => $c['endpoint_beta'] ?? null,
            'endpoint_produccion' => $c['endpoint_produccion'] ?? null,
            'ruc'                 => Setting::get('fe_ruc', $c['ruc'] ?? '20000000001'),
            'clave_sol_user'      => Setting::get('fe_sol_user', $c['clave_sol_user'] ?? 'MODDATOS'),
            'clave_sol_pass'      => Setting::get('fe_sol_pass', $c['clave_sol_pass'] ?? 'MODDATOS'),
            'certificate_path'    => Setting::get('fe_cert_path', $c['certificate_path'] ?? ''),
            'razon_social'        => Setting::get('fe_razon_social', $c['razon_social'] ?? ''),
            'nombre_comercial'    => Setting::get('fe_nombre_comercial', $c['nombre_comercial'] ?? ''),
            'ubigeo'              => Setting::get('fe_ubigeo', $c['ubigeo'] ?? '150101'),
            'departamento'        => Setting::get('fe_departamento', $c['departamento'] ?? 'LIMA'),
            'provincia'           => Setting::get('fe_provincia', $c['provincia'] ?? 'LIMA'),
            'distrito'            => Setting::get('fe_distrito', $c['distrito'] ?? 'LIMA'),
            'direccion'           => Setting::get('fe_direccion', $c['direccion'] ?? ''),
        ];
    }

    /** ¿El certificado configurado existe en disco? (para el estado en la UI) */
    public static function certificateExists(): bool
    {
        $path = (string) self::pe()['certificate_path'];
        return $path !== '' && is_file($path);
    }
}
