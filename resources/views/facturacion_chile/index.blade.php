@extends('layouts.app')

@section('title', 'Facturación Chile · DTE')

@section('content')
    <div class="max-w-7xl mx-auto">

        {{-- Cabecera --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-700 via-blue-800 to-rose-900 text-white shadow-lg mb-5">
            <svg class="absolute right-0 top-0 h-full w-1/2 opacity-10" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="170" cy="30" r="90" stroke="white" stroke-width="10"/>
                <circle cx="150" cy="120" r="60" stroke="white" stroke-width="8"/>
            </svg>
            <div class="relative flex items-center gap-5 p-6">
                <div class="hidden sm:flex w-14 h-14 rounded-2xl bg-white/15 backdrop-blur items-center justify-center ring-1 ring-white/20 shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13.5h6M9 16.5h4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-2xl font-extrabold tracking-tight">Documentos Tributarios Electrónicos</h1>
                        <span class="inline-flex h-5 w-7 rounded-sm overflow-hidden ring-1 ring-white/40 shadow-sm" title="Chile">
                            <span class="w-1/2 bg-blue-600"></span>
                            <span class="w-1/2 bg-white"></span>
                        </span>
                        <span class="text-sm font-bold text-blue-50">Chile · SII</span>
                    </div>
                    <p class="opacity-90 text-sm mt-1">Emisión, consulta, notas de crédito y reintentos de boletas y facturas ante el SII.</p>
                </div>
                <form method="POST" action="{{ route('facturacionchile.reintentar-lote') }}" class="shrink-0">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-white/15 hover:bg-white/25 px-4 py-2 text-sm font-bold ring-1 ring-white/25 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                        Reintentar pendientes
                    </button>
                </form>
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" action="{{ route('facturacionchile.index') }}" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Desde</label>
                <input type="date" name="desde" value="{{ $filtros['desde'] }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Hasta</label>
                <input type="date" name="hasta" value="{{ $filtros['hasta'] }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Tipo DTE</label>
                <select name="tipo_dte" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    <option value="">Todos</option>
                    <option value="boleta" @selected($filtros['tipo_dte'] === 'boleta')>Boleta (39)</option>
                    <option value="factura" @selected($filtros['tipo_dte'] === 'factura')>Factura (33)</option>
                    <option value="nota_credito" @selected($filtros['tipo_dte'] === 'nota_credito')>Nota de Crédito (61)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wide">Estado</label>
                <select name="estado" class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none">
                    <option value="">Todos</option>
                    @foreach(['pendiente','enviando','aceptado','observado','rechazado','anulado','error'] as $st)
                        <option value="{{ $st }}" @selected($filtros['estado'] === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2.5 transition">Filtrar</button>
            <a href="{{ route('facturacionchile.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700 self-center">Limpiar</a>
        </form>

        {{-- Tabla --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-4 py-3 font-semibold">ID</th>
                            <th class="px-4 py-3 font-semibold">Tipo DTE</th>
                            <th class="px-4 py-3 font-semibold">Folio</th>
                            <th class="px-4 py-3 font-semibold">Venta</th>
                            <th class="px-4 py-3 font-semibold">Cliente</th>
                            <th class="px-4 py-3 font-semibold text-right">Total</th>
                            <th class="px-4 py-3 font-semibold">Estado</th>
                            <th class="px-4 py-3 font-semibold">Fecha</th>
                            <th class="px-4 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($documentos as $dte)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-4 py-3 text-slate-500">#{{ $dte->id }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-lg px-2 py-1 text-xs font-bold ring-1 ring-inset
                                        {{ $dte->document_type === 'factura' ? 'bg-blue-50 text-blue-700 ring-blue-200' : ($dte->document_type === 'nota_credito' ? 'bg-rose-50 text-rose-700 ring-rose-200' : 'bg-emerald-50 text-emerald-700 ring-emerald-200') }}">
                                        {{ match ($dte->document_type) { 'factura' => 'Factura (33)', 'nota_credito' => 'NC (61)', default => 'Boleta (39)' } }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-700">{{ $dte->series }}-{{ $dte->number }}</td>
                                <td class="px-4 py-3">
                                    @if ($dte->sale)
                                        <a href="{{ route('ventas.show', $dte->sale_id) }}" class="text-blue-600 hover:underline font-medium">Venta #{{ $dte->sale->number }}</a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $dte->sale?->customer?->name ?? 'Consumidor Final' }}</td>
                                <td class="px-4 py-3 text-right font-semibold">${{ number_format((float) ($dte->sale?->total ?? 0)) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset {{ $dte->estadoColor() }}">{{ $dte->estadoLabel() }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $dte->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('facturacionchile.ver', $dte->id) }}" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                                            Ver
                                        </a>
                                        @if ($dte->reintentable())
                                            <form method="POST" action="{{ route('facturacionchile.reintentar', $dte->id) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-lg border border-amber-200 px-2.5 py-1 text-xs font-semibold text-amber-700 hover:bg-amber-50 transition">
                                                    Reintentar
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                                    No hay DTEs para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($documentos->hasPages())
                <div class="px-4 py-3 border-t border-slate-100">
                    {{ $documentos->appends($filtros)->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection