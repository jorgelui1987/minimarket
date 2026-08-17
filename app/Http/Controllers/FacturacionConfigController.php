<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\Billing\BillingSettings;
use App\Services\Billing\ConnectionTester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FacturacionConfigController extends Controller
{
    /** Claves de settings que administra este módulo con sus defaults. */
    private function keys(): array
    {
        $pe = BillingSettings::pe();

        return [
            'fe_enabled'          => BillingSettings::enabled() ? '1' : '0',
            'fe_auto_emit'        => BillingSettings::autoEmit() ? '1' : '0',
            'fe_driver'           => BillingSettings::driver(),
            'fe_mode'             => $pe['mode'],
            'fe_ruc'              => $pe['ruc'],
            'fe_sol_user'         => $pe['clave_sol_user'],
            'fe_sol_pass'         => $pe['clave_sol_pass'],
            'fe_cert_path'        => $pe['certificate_path'],
            'fe_razon_social'     => $pe['razon_social'],
            'fe_nombre_comercial' => $pe['nombre_comercial'],
            'fe_ubigeo'           => $pe['ubigeo'],
            'fe_departamento'     => $pe['departamento'],
            'fe_provincia'        => $pe['provincia'],
            'fe_distrito'         => $pe['distrito'],
            'fe_direccion'        => $pe['direccion'],
            'fe_rest_url'         => BillingSettings::restUrl(),
            'fe_rest_token'       => BillingSettings::restToken(),
        ];
    }

    public function edit(): View
    {
        $settings = $this->keys();
        $certOk = BillingSettings::certificateExists();

        return view('facturacion.config', compact('settings', 'certOk'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fe_enabled'          => ['nullable', 'boolean'],
            'fe_auto_emit'        => ['nullable', 'boolean'],
            'fe_driver'           => ['required', 'in:null,local,rest'],
            'fe_mode'             => ['required', 'in:beta,produccion'],
            'fe_ruc'              => ['required', 'string', 'size:11'],
            'fe_sol_user'         => ['nullable', 'string', 'max:50'],
            'fe_sol_pass'         => ['nullable', 'string', 'max:100'],
            'fe_cert_path'        => ['nullable', 'string', 'max:255'],
            'fe_razon_social'     => ['required', 'string', 'max:255'],
            'fe_nombre_comercial' => ['nullable', 'string', 'max:255'],
            'fe_ubigeo'           => ['nullable', 'string', 'max:6'],
            'fe_departamento'     => ['nullable', 'string', 'max:60'],
            'fe_provincia'        => ['nullable', 'string', 'max:60'],
            'fe_distrito'         => ['nullable', 'string', 'max:60'],
            'fe_direccion'        => ['nullable', 'string', 'max:255'],
            'fe_rest_url'         => ['nullable', 'url', 'max:255'],
            'fe_rest_token'       => ['nullable', 'string', 'max:255'],
        ]);

        // Checkboxes: normalizar a '1'/'0'.
        $data['fe_enabled']   = $request->boolean('fe_enabled') ? '1' : '0';
        $data['fe_auto_emit'] = $request->boolean('fe_auto_emit') ? '1' : '0';

        foreach ($data as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return redirect()->route('facturacion.config.edit')
            ->with('success', 'Configuración de facturación electrónica guardada.');
    }

    /** Prueba la conexión/config con SUNAT sin emitir un comprobante real. */
    public function test(ConnectionTester $tester): RedirectResponse
    {
        return redirect()->route('facturacion.config.edit')
            ->with('fe_test', $tester->run());
    }
}
