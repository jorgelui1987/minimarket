@extends('layouts.app')

@section('title', 'Ventas')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Ventas</h2>
            <p class="text-sm text-slate-500">Historial de comprobantes emitidos</p>
        </div>
        <a href="{{ route('pos') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84"/></svg>
            Nueva Venta (POS)
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Ventas de hoy</p>
            <p class="text-2xl font-extrabold text-emerald-600">S/ {{ number_format($todayTotal, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Comprobantes de hoy</p>
            <p class="text-2xl font-extrabold text-slate-800">{{ $todayCount }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="p-4 border-b border-slate-100 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Pago</label>
                <select name="payment" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                    <option value="">Todos</option>
                    @foreach (['efectivo', 'tarjeta', 'yape', 'plin'] as $pm)
                        <option value="{{ $pm }}" {{ request('payment') === $pm ? 'selected' : '' }}>{{ ucfirst($pm) }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrar</button>
            <a href="{{ route('ventas.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">Limpiar</a>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Comprobante</th>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Cliente</th>
                        <th class="px-5 py-3 font-semibold text-center">Ítems</th>
                        <th class="px-5 py-3 font-semibold">Pago</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sales as $sale)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ ucfirst($sale->document_type) }}</p>
                                <p class="text-xs text-slate-400">{{ $sale->series }}-{{ $sale->number }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $sale->customer?->name ?? 'Público general' }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $sale->items_count }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">{{ ucfirst($sale->payment_method) }}</span>
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">S/ {{ number_format($sale->total, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('ventas.show', $sale) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No hay ventas en este rango.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $sales->links() }}</div>
    </div>
@endsection
