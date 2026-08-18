@extends('layouts.app')

@section('title', 'Detalle de Compra')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('compras.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a compras</a>

        <div class="mt-3 rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <div class="flex flex-wrap justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-extrabold text-slate-800">Compra #{{ str_pad($compra->id, 5, '0', STR_PAD_LEFT) }}</h2>
                    <p class="text-sm text-slate-500">{{ $compra->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="text-right text-sm text-slate-500">
                    <p><span class="font-semibold text-slate-700">Proveedor:</span> {{ $compra->supplier?->name ?? 'Sin proveedor' }}</p>
                    <p><span class="font-semibold text-slate-700">Documento:</span> {{ $compra->document ?: '—' }}</p>
                    <p><span class="font-semibold text-slate-700">Registró:</span> {{ $compra->user?->name ?? '—' }}</p>
                </div>
            </div>

            {{-- Fotos de la compra --}}
            @if ($compra->photo_receipt || $compra->photo_products)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    @if ($compra->photo_receipt)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="text-xs font-semibold text-slate-500 mb-2">📷 Foto de la boleta</p>
                            <a href="{{ asset('storage/' . $compra->photo_receipt) }}" target="_blank">
                                <img src="{{ asset('storage/' . $compra->photo_receipt) }}" alt="Foto boleta" class="w-full max-h-64 object-cover rounded-lg">
                            </a>
                        </div>
                    @endif
                    @if ($compra->photo_products)
                        <div class="rounded-xl border border-slate-200 p-3">
                            <p class="text-xs font-semibold text-slate-500 mb-2">📦 Foto de los productos</p>
                            <a href="{{ asset('storage/' . $compra->photo_products) }}" target="_blank">
                                <img src="{{ asset('storage/' . $compra->photo_products) }}" alt="Foto productos" class="w-full max-h-64 object-cover rounded-lg">
                            </a>
                        </div>
                    @endif
                </div>
            @endif

            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-4 py-2 font-semibold">Producto</th>
                        <th class="px-4 py-2 font-semibold text-right">Costo</th>
                        <th class="px-4 py-2 font-semibold text-center">Cantidad</th>
                        <th class="px-4 py-2 font-semibold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($compra->items as $item)
                        <tr>
                            <td class="px-4 py-2 text-slate-800">{{ $item->product?->name ?? 'Producto eliminado' }}</td>
                            <td class="px-4 py-2 text-right text-slate-500">S/ {{ number_format($item->cost, 2) }}</td>
                            <td class="px-4 py-2 text-center text-slate-600">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                            <td class="px-4 py-2 text-right font-medium text-slate-800">S/ {{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200">
                        <td colspan="3" class="px-4 py-3 text-right font-bold text-slate-700">Total</td>
                        <td class="px-4 py-3 text-right font-extrabold text-brand-600 text-lg">S/ {{ number_format($compra->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
