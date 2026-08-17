<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = [];
        foreach (Setting::defaults() as $key => $default) {
            $settings[$key] = Setting::get($key, $default);
        }

        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:11'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'currency' => ['required', 'string', 'max:5'],
            'igv_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'ticket_footer' => ['nullable', 'string', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::set($key, (string) ($value ?? ''));
        }

        return redirect()->route('configuracion.edit')->with('success', 'Configuración guardada correctamente.');
    }
}
