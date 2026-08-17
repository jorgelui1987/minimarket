@extends('layouts.app')

@section('title', 'Configuración')

@section('content')
    <div class="max-w-3xl">
        <h2 class="text-xl font-extrabold text-slate-800 mb-1">Configuración del negocio</h2>
        <p class="text-sm text-slate-500 mb-5">Estos datos aparecen en los comprobantes y reportes</p>

        @include('partials.errors')

        <form method="POST" action="{{ route('configuracion.update') }}">
            @csrf @method('PUT')

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 mb-4">
                <h3 class="font-bold text-slate-800 mb-4">Datos generales</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del negocio <span class="text-red-500">*</span></label>
                        <input type="text" name="business_name" value="{{ old('business_name', $settings['business_name']) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">RUC</label>
                        <input type="text" name="ruc" value="{{ old('ruc', $settings['ruc']) }}" maxlength="11"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $settings['phone']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                        <input type="email" name="email" value="{{ old('email', $settings['email']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $settings['address']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 mb-4">
                <h3 class="font-bold text-slate-800 mb-4">Ventas y comprobantes</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Símbolo de moneda <span class="text-red-500">*</span></label>
                        <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" required maxlength="5"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">IGV (%) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" name="igv_percent" value="{{ old('igv_percent', $settings['igv_percent']) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Mensaje al pie del ticket</label>
                        <input type="text" name="ticket_footer" value="{{ old('ticket_footer', $settings['ticket_footer']) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>
            </div>

            <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar Configuración</button>
        </form>
    </div>
@endsection
