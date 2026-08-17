@extends('layouts.app')

@section('title', 'Kardex de Inventario')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Kardex de Inventario</h2>
            <p class="text-sm text-slate-500">Historial de movimientos de stock</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a alertas</a>
    </div>

    {{-- Ajuste manual --}}
    <div id="ajuste" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 mb-6">
        <h3 class="font-bold text-slate-800 mb-3">Ajuste manual de stock</h3>
        <form method="POST" action="{{ route('inventario.adjust') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Producto</label>
                <select name="product_id" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand-500">
                    <option value="">Selecciona...</option>
                    @foreach ($products as $p)
                        <option value="{{ $p->id }}" {{ (string) $productId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nuevo stock</label>
                <input type="number" name="new_stock" min="0" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo (opcional)</label>
                <input type="text" name="note" placeholder="Merma, conteo físico..." class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand-500">
            </div>
            <div class="sm:col-span-4">
                <button class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">Registrar ajuste</button>
            </div>
        </form>
    </div>

    {{-- Filtros --}}
    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <form method="GET" class="p-4 border-b border-slate-100 flex flex-wrap gap-3">
            <select name="product_id" class="rounded-lg border border-slate-200 px-4 py-2 text-sm outline-none focus:border-brand-500">
                <option value="">Todos los productos</option>
                @foreach ($products as $p)
                    <option value="{{ $p->id }}" {{ (string) $productId === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
            <select name="type" class="rounded-lg border border-slate-200 px-4 py-2 text-sm outline-none focus:border-brand-500">
                <option value="">Todos los movimientos</option>
                @foreach (['entrada' => 'Entradas', 'salida' => 'Salidas', 'ajuste' => 'Ajustes'] as $v => $l)
                    <option value="{{ $v }}" {{ request('type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filtrar</button>
        </form>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Fecha</th>
                        <th class="px-5 py-3 font-semibold">Producto</th>
                        <th class="px-5 py-3 font-semibold">Tipo</th>
                        <th class="px-5 py-3 font-semibold text-center">Cambio</th>
                        <th class="px-5 py-3 font-semibold text-center">Stock final</th>
                        <th class="px-5 py-3 font-semibold">Detalle</th>
                        <th class="px-5 py-3 font-semibold">Usuario</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $m->product?->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                @php $color = ['entrada' => 'emerald', 'salida' => 'red', 'ajuste' => 'blue'][$m->type] ?? 'slate'; @endphp
                                <span class="rounded-full bg-{{ $color }}-50 px-2.5 py-0.5 text-xs font-semibold text-{{ $color }}-600 capitalize">{{ $m->type }}</span>
                            </td>
                            <td class="px-5 py-3 text-center font-semibold {{ $m->quantity >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $m->quantity > 0 ? '+' : '' }}{{ $m->quantity }}</td>
                            <td class="px-5 py-3 text-center text-slate-700">{{ $m->stock_after }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $m->note ?? ucfirst($m->source) }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $m->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400">No hay movimientos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $movements->links() }}</div>
    </div>
@endsection
