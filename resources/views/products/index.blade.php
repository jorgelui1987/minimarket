@extends('layouts.app')

@section('title', 'Productos')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Productos</h2>
            <p class="text-sm text-slate-500">Catálogo, precios y stock</p>
        </div>
        <a href="{{ route('productos.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo Producto
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="p-4 border-b border-slate-100 flex flex-wrap gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nombre o código..."
                class="flex-1 min-w-48 rounded-lg border border-slate-200 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
            <select name="category" class="rounded-lg border border-slate-200 px-4 py-2 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                <option value="">Todas las categorías</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrar</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Categoría</th>
                        <th class="px-5 py-3 font-semibold text-right">Costo</th>
                        <th class="px-5 py-3 font-semibold text-right">Precio</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock</th>
                        <th class="px-5 py-3 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($products as $p)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3">
                                <p class="font-medium text-slate-800">{{ $p->name }}</p>
                                <p class="text-xs text-slate-400">{{ $p->barcode ?: 'Sin código' }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-500">{{ $p->category?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($p->cost, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold text-slate-800">S/ {{ number_format($p->price, 2) }}</td>
                            <td class="px-5 py-3 text-center">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $p->isLowStock() ? 'bg-red-50 text-red-600' : 'bg-emerald-50 text-emerald-700' }}">
                                    {{ $p->stock }} {{ $p->unit }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('productos.edit', $p) }}" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-600" title="Editar">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('productos.destroy', $p) }}" onsubmit="return confirm('¿Eliminar este producto?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">No se encontraron productos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $products->links() }}</div>
    </div>
@endsection
