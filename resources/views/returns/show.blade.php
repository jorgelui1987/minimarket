@extends('layouts.app')

@section('title', 'Detalle de devolución')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('devoluciones.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a devoluciones</a>

        <div class="mt-3 rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-800">Nota de crédito #{{ $devolucion->id }}</h2>
                    <p class="text-sm text-slate-500">
                        Venta {{ $devolucion->sale ? $devolucion->sale->series.'-'.$devolucion->sale->number : '—' }}
                        · {{ $devolucion->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <span class="rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-600">Reembolso</span>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr><th class="px-6 py-2 font-semibold">Producto</th><th class="px-6 py-2 font-semibold text-center">Cant.</th><th class="px-6 py-2 font-semibold text-right">Precio</th><th class="px-6 py-2 font-semibold text-right">Subtotal</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($devolucion->items as $it)
                        <tr>
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $it->product_name }}</td>
                            <td class="px-6 py-3 text-center text-slate-600">{{ $it->quantity }}</td>
                            <td class="px-6 py-3 text-right text-slate-600">S/ {{ number_format($it->price, 2) }}</td>
                            <td class="px-6 py-3 text-right font-medium text-slate-800">S/ {{ number_format($it->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-200">
                        <td colspan="3" class="px-6 py-3 text-right font-bold text-slate-700">Total reembolsado</td>
                        <td class="px-6 py-3 text-right font-extrabold text-red-600">S/ {{ number_format($devolucion->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>

            @if ($devolucion->reason)
                <div class="px-6 py-4 border-t border-slate-100 text-sm text-slate-500">
                    <span class="font-semibold text-slate-600">Motivo:</span> {{ $devolucion->reason }}
                </div>
            @endif
            <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400">
                Registrada por {{ $devolucion->user?->name ?? '—' }}. El stock fue repuesto y, si había caja abierta, se registró la salida de efectivo.
            </div>
        </div>
    </div>
@endsection
