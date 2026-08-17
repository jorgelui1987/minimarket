@extends('layouts.app')

@section('title', 'Devoluciones')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Devoluciones</h2>
            <p class="text-sm text-slate-500">Notas de crédito y reembolsos de ventas</p>
        </div>
        <a href="{{ route('devoluciones.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nueva devolución
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Venta</th>
                        <th class="px-5 py-3 font-semibold text-center">Ítems</th>
                        <th class="px-5 py-3 font-semibold">Motivo</th>
                        <th class="px-5 py-3 font-semibold">Usuario</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($returns as $r)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $r->sale ? $r->sale->series.'-'.$r->sale->number : '—' }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $r->items_count }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $r->reason ?: '—' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $r->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-red-600">- S/ {{ number_format($r->total, 2) }}</td>
                            <td class="px-5 py-3 text-right"><a href="{{ route('devoluciones.show', $r) }}" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-brand-600 border border-brand-200 hover:bg-brand-50">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">Aún no hay devoluciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $returns->links() }}</div>
    </div>
@endsection
