@extends('layouts.app')

@section('title', 'Facturación Electrónica')

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- ══════════════ BANNER PERÚ / SUNAT ══════════════ --}}
        @php($esCL = ($pais ?? 'PE') === 'CL')
        <div class="relative overflow-hidden rounded-2xl {{ $esCL ? 'bg-gradient-to-r from-blue-700 via-blue-800 to-rose-900' : 'bg-gradient-to-r from-emerald-600 via-emerald-700 to-emerald-900' }} text-white shadow-lg mb-6">
            {{-- textura decorativa --}}
            <svg class="absolute right-0 top-0 h-full w-1/2 opacity-10" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="170" cy="30" r="90" stroke="white" stroke-width="10"/>
                <circle cx="150" cy="120" r="60" stroke="white" stroke-width="8"/>
            </svg>

            <div class="relative flex items-center gap-5 p-6 sm:p-8">
                <div class="hidden sm:flex w-16 h-16 rounded-2xl bg-white/15 backdrop-blur items-center justify-center ring-1 ring-white/20 shrink-0">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5h6M9 16.5h4"/>
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-extrabold tracking-tight">Facturación Electrónica</h1>
                        {{-- Bandera según país --}}
                        @if ($esCL)
                        <span class="inline-flex h-5 w-7 rounded-sm overflow-hidden ring-1 ring-white/40 shadow-sm" title="Chile">
                            <span class="w-1/2 bg-blue-600"></span>
                            <span class="w-1/2 bg-white"></span>
                        </span>
                        <span class="text-sm font-bold text-blue-50">Chile</span>
                        @else
                        <span class="inline-flex h-5 w-7 rounded-sm overflow-hidden ring-1 ring-white/40 shadow-sm" title="Perú">
                            <span class="w-1/3 bg-red-600"></span>
                            <span class="w-1/3 bg-white"></span>
                            <span class="w-1/3 bg-red-600"></span>
                        </span>
                        <span class="text-sm font-bold text-emerald-50">Perú</span>
                        @endif
                    </div>
                    <p class="opacity-90 text-sm mt-1.5">
                        @if ($esCL)
                        Emisión de documentos tributarios ante <strong class="font-semibold">SII</strong> · DTE · Facturas, boletas y notas de crédito · Folios CAF
                        @else
                        Emisión de comprobantes electrónicos ante <strong class="font-semibold">SUNAT</strong> · UBL 2.1 · Boletas, facturas y notas de crédito
                        @endif
                    </p>
                </div>

                <div class="hidden md:flex flex-col items-end gap-2 shrink-0">
                    <span class="rounded-lg bg-white/15 px-3 py-1.5 text-sm font-extrabold tracking-wide ring-1 ring-white/25">{{ $esCL ? 'SII' : 'SUNAT' }}</span>
                    <span class="text-[11px] opacity-80">{{ $esCL ? 'DTE · Folios CAF' : 'Comprobantes de Pago Electrónicos' }}</span>
                </div>
            </div>

            {{-- franja de estado --}}
            <div class="relative border-t border-white/10 bg-black/10 px-6 sm:px-8 py-3 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $settings['fe_enabled'] === '1' ? 'bg-emerald-400/20 text-emerald-50 ring-emerald-300/40' : 'bg-white/10 text-emerald-100/80 ring-white/20' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $settings['fe_enabled'] === '1' ? 'bg-emerald-300' : 'bg-slate-300' }}"></span>
                    {{ $settings['fe_enabled'] === '1' ? 'Habilitada' : 'Deshabilitada' }}
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-white/10 text-white ring-white/20">Driver: {{ $settings['fe_driver'] }}</span>
                @if (!$esCL)
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $settings['fe_mode'] === 'produccion' ? 'bg-amber-400/25 text-amber-50 ring-amber-300/40' : 'bg-white/10 text-white ring-white/20' }}">Modo: {{ $settings['fe_mode'] }}</span>
                @endif
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $certOk ? 'bg-emerald-400/20 text-emerald-50 ring-emerald-300/40' : 'bg-rose-400/25 text-rose-50 ring-rose-300/40' }}">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $certOk ? 'm4.5 12.75 6 6 9-13.5' : 'M6 18 18 6M6 6l12 12' }}"/></svg>
                    Certificado {{ $certOk ? 'encontrado' : 'no encontrado' }}
                </span>

                @if (!$esCL)
                <form method="POST" action="{{ route('facturacion.config.test') }}" class="ml-auto">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-white/15 hover:bg-white/25 px-3 py-1.5 text-xs font-bold ring-1 ring-white/25 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Probar conexión con SUNAT
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Resultado de la prueba de conexión --}}
        @if (session('fe_test'))
            @php($t = session('fe_test'))
            @php($tone = ['ok' => ['bg-emerald-50','border-emerald-200','text-emerald-800'], 'warn' => ['bg-amber-50','border-amber-200','text-amber-800'], 'error' => ['bg-rose-50','border-rose-200','text-rose-800']][$t['overall']])
            <div class="rounded-2xl border shadow-sm p-5 mb-4 {{ $tone[0] }} {{ $tone[1] }}">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-5 h-5 {{ $tone[2] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['overall'] === 'ok' ? 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' : 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z' }}"/></svg>
                    <h3 class="font-bold {{ $tone[2] }}">
                        {{ $t['overall'] === 'ok' ? 'Todo listo para emitir' : ($t['overall'] === 'warn' ? 'Revisar configuración' : 'Hay problemas por resolver') }}
                    </h3>
                </div>
                <ul class="space-y-1.5">
                    @foreach ($t['checks'] as $c)
                        <li class="flex items-start gap-2 text-sm">
                            <svg class="w-4 h-4 mt-0.5 shrink-0 {{ $c['status'] === 'ok' ? 'text-emerald-600' : ($c['status'] === 'warn' ? 'text-amber-600' : 'text-rose-600') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['status'] === 'ok' ? 'm4.5 12.75 6 6 9-13.5' : ($c['status'] === 'warn' ? 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z' : 'M6 18 18 6M6 6l12 12') }}"/>
                            </svg>
                            <span class="text-slate-700"><strong>{{ $c['label'] }}:</strong> {{ $c['detail'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('partials.errors')

        <form method="POST" action="{{ route('facturacion.config.update') }}">
            @csrf @method('PUT')

            {{-- ══════════ País fiscal ══════════ --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden mb-4">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">País fiscal</h3>
                        <p class="text-xs text-slate-500">Selecciona el país donde emites comprobantes electrónicos</p>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">País de facturación</label>
                            <select name="fe_pais" id="fe_pais" onchange="this.form.action = '{{ route('facturacion.config.edit') }}?pais=' + this.value + '&preview=1'; this.form.submit();" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                                <option value="PE" @selected(($pais ?? 'PE') === 'PE')>🇵🇪 Perú (SUNAT)</option>
                                <option value="CL" @selected(($pais ?? 'PE') === 'CL')>🇨🇱 Chile (SII)</option>
                            </select>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-3">Al cambiar de país se mostrarán los campos de configuración correspondientes.</p>
                </div>
            </div>

            {{-- ══════════ Estado y modo ══════════ --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden mb-4">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-9 h-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Estado y modo</h3>
                        <p class="text-xs text-slate-500">Activación, forma de emisión y entorno de SUNAT</p>
                    </div>
                </div>
                <div class="p-6 space-y-3">
                    <label class="flex items-center gap-3 rounded-xl border border-slate-100 px-4 py-3 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="fe_enabled" value="1" @checked($settings['fe_enabled'] === '1')
                            class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-200">
                        <span class="text-sm">
                            <span class="block font-semibold text-slate-700">Habilitar facturación electrónica</span>
                            <span class="block text-xs text-slate-500">Si está desactivada, las ventas no generan comprobante ante SUNAT.</span>
                        </span>
                    </label>
                    <label class="flex items-center gap-3 rounded-xl border border-slate-100 px-4 py-3 hover:bg-slate-50 cursor-pointer transition">
                        <input type="checkbox" name="fe_auto_emit" value="1" @checked($settings['fe_auto_emit'] === '1')
                            class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-200">
                        <span class="text-sm">
                            <span class="block font-semibold text-slate-700">Emitir automáticamente al cerrar la venta</span>
                            <span class="block text-xs text-slate-500">Cada boleta o factura se envía apenas se registra en el POS.</span>
                        </span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Driver de emisión</label>
                            <select name="fe_driver" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                                <option value="null" @selected($settings['fe_driver'] === 'null')>Ninguno (no emite, deja pendiente)</option>
                                <option value="local" @selected($settings['fe_driver'] === 'local')>Local (Greenter, mismo servidor)</option>
                                <option value="rest" @selected($settings['fe_driver'] === 'rest')>Servicio externo (REST)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Entorno SUNAT</label>
                            <select name="fe_mode" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                                <option value="beta" @selected($settings['fe_mode'] === 'beta')>Beta (homologación / pruebas)</option>
                                <option value="produccion" @selected($settings['fe_mode'] === 'produccion')>Producción</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══════════ Datos del emisor ══════════ --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden mb-4">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-9 h-9 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Datos del emisor</h3>
                        <p class="text-xs text-slate-500">Aparecen en el comprobante electrónico</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if ($esCL)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">RUT del emisor <span class="text-red-500">*</span></label>
                        <input type="text" name="fe_rut" value="{{ old('fe_rut', $settings['fe_rut']) }}" placeholder="76123456-7" maxlength="12" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social <span class="text-red-500">*</span></label>
                        <input type="text" name="fe_razon_social_cl" value="{{ old('fe_razon_social_cl', $settings['fe_razon_social_cl']) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Giro</label>
                        <input type="text" name="fe_giro" value="{{ old('fe_giro', $settings['fe_giro']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección fiscal</label>
                        <input type="text" name="fe_direccion_cl" value="{{ old('fe_direccion_cl', $settings['fe_direccion_cl']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    </div>
                    @else
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">RUC <span class="text-red-500">*</span></label>
                        <input type="text" name="fe_ruc" value="{{ old('fe_ruc', $settings['fe_ruc']) }}" maxlength="11" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social <span class="text-red-500">*</span></label>
                        <input type="text" name="fe_razon_social" value="{{ old('fe_razon_social', $settings['fe_razon_social']) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre comercial</label>
                        <input type="text" name="fe_nombre_comercial" value="{{ old('fe_nombre_comercial', $settings['fe_nombre_comercial']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección fiscal</label>
                        <input type="text" name="fe_direccion" value="{{ old('fe_direccion', $settings['fe_direccion']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    @endif
                    @if (!$esCL)
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Ubigeo</label>
                        <input type="text" name="fe_ubigeo" value="{{ old('fe_ubigeo', $settings['fe_ubigeo']) }}" maxlength="6"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Departamento</label>
                        <input type="text" name="fe_departamento" value="{{ old('fe_departamento', $settings['fe_departamento']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Provincia</label>
                        <input type="text" name="fe_provincia" value="{{ old('fe_provincia', $settings['fe_provincia']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Distrito</label>
                        <input type="text" name="fe_distrito" value="{{ old('fe_distrito', $settings['fe_distrito']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══════════ Credenciales SUNAT ══════════ --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden mb-4">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-9 h-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">{{ $esCL ? 'Credenciales SII' : 'Credenciales SUNAT' }}</h3>
                        <p class="text-xs text-slate-500">Clave SOL y certificado digital</p>
                    </div>
                </div>
                <div class="p-6">
                    @if ($esCL)
                    <div class="flex items-start gap-2 rounded-xl bg-sky-50 text-sky-800 px-4 py-3 text-xs mb-4 ring-1 ring-sky-100">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        <span>En <strong>Chile</strong> debes tener tu <strong>certificado digital</strong> (firma electrónica avanzada) y los <strong>folios CAF</strong> descargados del SII.</span>
                    </div>
                    @else
                    <div class="flex items-start gap-2 rounded-xl bg-sky-50 text-sky-800 px-4 py-3 text-xs mb-4 ring-1 ring-sky-100">
                        <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        <span>En <strong>beta</strong> puedes usar RUC <strong>20000000001</strong> con usuario y clave <strong>MODDATOS</strong>.</span>
                    </div>
                    @endif
                    @if ($esCL)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ruta del certificado (.pem)</label>
                            <input type="text" name="fe_cert_path_cl" value="{{ old('fe_cert_path_cl', $settings['fe_cert_path_cl']) }}" placeholder="storage/facturacion/cl/certificate.pem"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                            <p class="flex items-center gap-1.5 text-xs {{ $certOk ? 'text-emerald-600' : 'text-rose-600' }} mt-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $certOk ? 'm4.5 12.75 6 6 9-13.5' : 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z' }}"/></svg>
                                {{ $certOk ? 'Certificado encontrado en la ruta indicada.' : 'No se encontró el certificado en la ruta indicada.' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Password del certificado</label>
                            <input type="password" name="fe_cert_pass" value="{{ old('fe_cert_pass', $settings['fe_cert_pass']) }}" autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Directorio de folios CAF</label>
                            <input type="text" name="fe_caf_dir" value="{{ old('fe_caf_dir', $settings['fe_caf_dir']) }}" placeholder="storage/facturacion/cl/caf"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                        </div>
                    </div>
                    @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Usuario Clave SOL</label>
                            <input type="text" name="fe_sol_user" value="{{ old('fe_sol_user', $settings['fe_sol_user']) }}"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Clave SOL</label>
                            <input type="password" name="fe_sol_pass" value="{{ old('fe_sol_pass', $settings['fe_sol_pass']) }}" autocomplete="new-password"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Ruta del certificado (.pem)</label>
                            <input type="text" name="fe_cert_path" value="{{ old('fe_cert_path', $settings['fe_cert_path']) }}" placeholder="storage/facturacion/pe/certificate.pem"
                                class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                            <p class="flex items-center gap-1.5 text-xs {{ $certOk ? 'text-emerald-600' : 'text-rose-600' }} mt-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $certOk ? 'm4.5 12.75 6 6 9-13.5' : 'M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z' }}"/></svg>
                                {{ $certOk ? 'Certificado encontrado en la ruta indicada.' : 'No se encontró el certificado en la ruta indicada.' }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ══════════ Servicio externo (REST) ══════════ --}}
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden mb-6">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-100 bg-slate-50/60">
                    <div class="w-9 h-9 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 17.25v-.228a4.5 4.5 0 0 0-.12-1.03l-2.268-9.64a3.375 3.375 0 0 0-3.285-2.602H7.923a3.375 3.375 0 0 0-3.285 2.602l-2.268 9.64a4.5 4.5 0 0 0-.12 1.03v.228m19.5 0a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3m19.5 0a3 3 0 0 0-3-3H5.25a3 3 0 0 0-3 3m16.5 0h.008v.008h-.008v-.008Zm-3 0h.008v.008h-.008v-.008Z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800">Servicio externo (driver REST)</h3>
                        <p class="text-xs text-slate-500">Solo aplica si el driver es "Servicio externo (REST)"</p>
                    </div>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">URL del servicio</label>
                        <input type="text" name="fe_rest_url" value="{{ old('fe_rest_url', $settings['fe_rest_url']) }}" placeholder="http://localhost:8090"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Token</label>
                        <input type="password" name="fe_rest_token" value="{{ old('fe_rest_token', $settings['fe_rest_token']) }}" autocomplete="new-password"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 outline-none">
                    </div>
                </div>
            </div>

            {{-- ══════════ Barra de acciones ══════════ --}}
            <div class="sticky bottom-4 flex items-center justify-between gap-3 rounded-2xl bg-white/90 backdrop-blur border border-slate-200 shadow-lg px-5 py-3">
                <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver</a>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    Guardar configuración
                </button>
            </div>
        </form>
    </div>
@endsection
