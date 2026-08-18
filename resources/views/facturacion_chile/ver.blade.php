@extends('layouts.app')

@section('title', 'DTE #' . $dte->id)

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- Cabecera --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div class="flex items-center gap-3">
                <a href="{{ route('facturacionchile.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                    Volver
                </a>
                <h1 class="text-2xl font-extrabold text-slate-800">DTE #{{ $dte->id }}</h1>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset {{ $dte->estadoColor() }}">{{ $dte->estadoLabel() }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if ($dte->xml_path)
                    <a href="{{ route('facturacionchile.xml', $dte->id) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold px-3.5 py-2 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Descargar XML
                    </a>
                @endif
                @if ($dte->reintentable())
                    <form method="POST" action="{{ route('facturacionchile.reintentar', $dte->id) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 text-sm font-semibold px-3.5 py-2 hover:bg-amber-100 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                            Reintentar
                        </button>
                    </form>
                @endif
                @if ($dte->document_type !== 'nota_credito' && $dte->status !== 'anulado')
                    <form method="POST" action="{{ route('facturacionchile.nota-credito', $dte->id) }}" onsubmit="return confirm('¿Emitir Nota de Crédito para anular este DTE?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 text-sm font-semibold px-3.5 py-2 hover:bg-rose-100 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/></svg>
                            Nota de Crédito
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Datos del DTE --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Documento</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Tipo DTE</dt><dd class="font-semibold">{{ match ($dte->document_type) { 'factura' => 'Factura (33)', 'nota_credito' => 'Nota de Crédito (61)', default => 'Boleta (39)' } }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Folio</dt><dd class="font-mono font-semibold">{{ $dte->series }}-{{ $dte->number }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">País</dt><dd class="font-semibold">Chile (CL)</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Fecha emisión</dt><dd>{{ $dte->created_at->format('d/m/Y H:i') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">TrackId / Ext.</dt><dd class="font-mono text-xs">{{ $dte->external_id ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Venta asociada</h3>
                @if ($dte->sale)
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">Venta</dt><dd><a href="{{ route('ventas.show', $dte->sale_id) }}" class="text-blue-600 hover:underline font-semibold">#{{ $dte->sale->number }}</a></dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Cliente</dt><dd class="font-semibold">{{ $dte->sale->customer?->name ?? 'Consumidor Final' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">RUT</dt><dd class="font-mono">{{ $dte->sale->customer?->doc_number ?? '66666666-6' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Subtotal</dt><dd>${{ number_format((float) $dte->sale->subtotal) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">IVA (19%)</dt><dd>${{ number_format((float) $dte->sale->tax) }}</dd></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="text-slate-500 font-semibold">Total</dt><dd class="font-bold text-lg">${{ number_format((float) $dte->sale->total) }}</dd></div>
                    </dl>
                @else
                    <p class="text-sm text-slate-400">Sin venta asociada.</p>
                @endif
            </div>
        </div>

        {{-- Mensaje / observaciones --}}
        @if ($dte->message || $dte->observations)
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 mb-5">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Respuesta de la autoridad</h3>
                @if ($dte->message)
                    <p class="text-sm text-slate-700 mb-2">{{ $dte->message }}</p>
                @endif
                @if ($dte->observations)
                    <ul class="space-y-1">
                        @foreach ($dte->observations as $obs)
                            <li class="text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-1.5 ring-1 ring-amber-100">{{ is_array($obs) ? json_encode($obs) : $obs }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        {{-- Logs recientes --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50/60">
                <h3 class="font-bold text-slate-800">Bitácora del DTE</h3>
                <a href="{{ route('facturacionchile.logs', $dte->id) }}" class="text-sm font-semibold text-blue-600 hover:underline">Ver todos →</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse ($logs as $log)
                    <div class="px-5 py-3 flex items-start gap-3">
                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $log->event === 'error' ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700">{{ $log->event }}</p>
                            @if ($log->context)
                                <pre class="text-xs text-slate-400 mt-0.5 whitespace-pre-wrap">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 shrink-0">{{ $log->created_at->format('d/m H:i:s') }}</span>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-slate-400">Sin eventos registrados para este DTE.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection