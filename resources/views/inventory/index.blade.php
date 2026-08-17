@extends('layouts.app')

@section('title', 'Inventario')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Control de Inventario</h2>
            <p class="text-sm text-slate-500">Alertas de stock y estado general del inventario</p>
        </div>
        <a href="{{ route('inventario.kardex') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            Ver Kardex
        </a>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Productos</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ $totalProductos }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Valor del inventario</p>
            <p class="mt-1 text-2xl font-extrabold text-slate-800">S/ {{ number_format($valorInventario, 2) }}</p>
            <p class="text-xs text-slate-400">a costo</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white p-5 shadow-lg shadow-orange-500/20">
            <p class="text-sm text-white/80">Stock bajo</p>
            <p class="mt-1 text-3xl font-extrabold">{{ $stockBajo->count() }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-red-400 to-rose-600 text-white p-5 shadow-lg shadow-rose-500/20">
            <p class="text-sm text-white/80">Sin stock</p>
            <p class="mt-1 text-3xl font-extrabold">{{ $sinStock->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Sin stock --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                <h3 class="font-bold text-slate-800">Productos agotados</h3>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr><th class="px-5 py-2 font-semibold">Producto</th><th class="px-5 py-2 font-semibold text-center">Stock</th><th class="px-5 py-2 font-semibold text-right">Ajustar</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($sinStock as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 font-medium text-slate-800">{{ $p->name }}</td>
                                <td class="px-5 py-2.5 text-center"><span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-semibold text-red-600">{{ $p->stock }}</span></td>
                                <td class="px-5 py-2.5 text-right"><a href="{{ route('inventario.kardex', ['product_id' => $p->id]) }}#ajuste" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Ajustar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">No hay productos agotados. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Stock bajo --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                <h3 class="font-bold text-slate-800">Stock por debajo del mínimo</h3>
            </div>
            <div class="overflow-x-auto max-h-96">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr><th class="px-5 py-2 font-semibold">Producto</th><th class="px-5 py-2 font-semibold text-center">Stock</th><th class="px-5 py-2 font-semibold text-center">Mínimo</th><th class="px-5 py-2 font-semibold text-right">Ajustar</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($stockBajo as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 font-medium text-slate-800">{{ $p->name }}</td>
                                <td class="px-5 py-2.5 text-center"><span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600">{{ $p->stock }}</span></td>
                                <td class="px-5 py-2.5 text-center text-slate-500">{{ $p->min_stock }}</td>
                                <td class="px-5 py-2.5 text-right"><a href="{{ route('inventario.kardex', ['product_id' => $p->id]) }}#ajuste" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Ajustar</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Todo el inventario está por encima del mínimo. ✅</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
