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
        $cl = BillingSettings::cl();

        return [
            'fe_pais'             => BillingSettings::country(),
            'fe_enabled'          => BillingSettings::enabled() ? '1' : '0',
            'fe_auto_emit'        => BillingSettings::autoEmit() ? '1' : '0',
            'fe_driver'           => BillingSettings::driver(),

            // --- Perú ---
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

            // --- Chile ---
            'fe_rut'              => $cl['rut_emisor'],
            'fe_cert_path_cl'     => $cl['certificate_path'],
            'fe_cert_pass'        => $cl['certificate_pass'],
            'fe_caf_dir'          => $cl['caf_directory'],
            'fe_razon_social_cl'  => $cl['razon_social'],
            'fe_giro'             => $cl['giro'],
            'fe_direccion_cl'     => $cl['direccion'],
            'fe_comuna'           => $cl['comuna'],
            'fe_ciudad'           => $cl['ciudad'],

            'fe_rest_url'         => BillingSettings::restUrl(),
            'fe_rest_token'       => BillingSettings::restToken(),
        ];
    }

    public function edit(Request $request): View
    {
        $settings = $this->keys();
        // El selector usa ?pais= para previsualizar el país sin guardar.
        $pais = strtoupper((string) $request->query('pais', $settings['fe_pais']));
        $certOk = $pais === 'CL'
            ? BillingSettings::cl()['certificate_path'] !== '' && is_file(BillingSettings::cl()['certificate_path'])
            : BillingSettings::certificateExists();

        return view('facturacion.config', compact('settings', 'certOk', 'pais'));
    }

    public function update(Request $request): RedirectResponse
    {
        $pais = strtoupper((string) $request->input('fe_pais', 'PE'));

        if ($pais === 'CL') {
            $data = $request->validate([
                'fe_pais'          => ['required', 'in:PE,CL'],
                'fe_enabled'       => ['nullable', 'boolean'],
                'fe_auto_emit'     => ['nullable', 'boolean'],
                'fe_driver'        => ['required', 'in:null,local,rest'],
                'fe_rut'           => ['required', 'string', 'max:12'],
                'fe_cert_path_cl'  => ['nullable', 'string', 'max:255'],
                'fe_cert_pass'     => ['nullable', 'string', 'max:100'],
                'fe_caf_dir'       => ['nullable', 'string', 'max:255'],
                'fe_razon_social_cl' => ['required', 'string', 'max:255'],
                'fe_giro'          => ['nullable', 'string', 'max:100'],
                'fe_direccion_cl'  => ['nullable', 'string', 'max:255'],
                'fe_comuna'        => ['nullable', 'string', 'max:60'],
                'fe_ciudad'        => ['nullable', 'string', 'max:60'],
                'fe_rest_url'      => ['nullable', 'url', 'max:255'],
                'fe_rest_token'    => ['nullable', 'string', 'max:255'],
            ]);
        } else {
            $data = $request->validate([
                'fe_pais'             => ['required', 'in:PE,CL'],
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
        }

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
