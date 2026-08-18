@extends('layouts.app')

@section('title', 'Compras')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Compras</h2>
            <p class="text-sm text-slate-500">Ingreso de mercadería de proveedores</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('compras.movil') }}" class="inline-flex items-center gap-2 rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-slate-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"/></svg>
                📱 Móvil
            </a>
            <a href="{{ route('compras.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nueva Compra
            </a>
        </div>
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
                        <th class="px-5 py-3 font-semibold text-center">Foto</th>
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
                            <td class="px-5 py-3 text-center">
                                @if ($purchase->photo_receipt)
                                    <a href="{{ asset('storage/' . $purchase->photo_receipt) }}" target="_blank" class="inline-block" title="Ver foto boleta">
                                        <img src="{{ asset('storage/' . $purchase->photo_receipt) }}" alt="Foto" class="w-10 h-10 rounded-lg object-cover border border-slate-200">
                                    </a>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">S/ {{ number_format($purchase->total, 2) }}</td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('compras.show', $purchase) }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No hay compras registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $purchases->links() }}</div>
    </div>
@endsection
