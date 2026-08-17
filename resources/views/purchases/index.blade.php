@extends('layouts.app')

@section('title', 'Compras')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Compras</h2>
            <p class="text-sm text-slate-500">Ingreso de mercadería de proveedores</p>
        </div>
        <a href="{{ route('compras.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nueva Compra
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Proveedor</th>
                        <th class="px-5 py-3 font-semibold">Documento</th>
                        <th class="px-5 py-3 font-semibold text-center">Ítems</th>
                        <th class="px-5 py-3 font-semibold text-right">Total</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $purchase->supplier?->name ?? 'Sin proveedor' }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $purchase->document ?: '—' }}</td>
                            <td class="px-5 py-3 text-center text-slate-600">{{ $purchase->items_count }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">S/ {{ number_format($purchase->total, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('compras.show', $purchase) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No hay compras registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $purchases->links() }}</div>
    </div>
@endsection
