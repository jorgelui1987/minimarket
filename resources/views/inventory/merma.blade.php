@extends('layouts.app')

@section('title', 'Reporte de Mermas')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Reporte de Mermas</h2>
            <p class="text-sm text-slate-500">Control de pérdidas de inventario (verdulería)</p>
        </div>
        <a href="{{ route('inventario.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">
            ← Volver a Inventario
        </a>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('inventario.mermas') }}" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Desde</label>
                <input type="date" name="desde" value="{{ $desde?->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta?->format('Y-m-d') }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">Filtrar</button>
            </div>
        </div>
    </form>

    {{-- Resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
        <div class="rounded-2xl bg-gradient-to-br from-rose-400 to-red-600 text-white p-5 shadow-lg shadow-rose-500/20">
            <p class="text-sm text-white/80">Total merma</p>
            <p class="mt-1 text-3xl font-extrabold">{{ number_format($totalMerma, 3) }}</p>
            <p class="text-xs text-white/70">kg / unidades perdidas</p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Productos con merma</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ $porProducto->count() }}</p>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <p class="text-sm text-slate-500">Motivos distintos</p>
            <p class="mt-1 text-3xl font-extrabold text-slate-800">{{ $porMotivo->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
        {{-- Por producto --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Merma por producto</h3>
            </div>
            <div class="overflow-x-auto max-h-80">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr><th class="px-5 py-2 font-semibold">Producto</th><th class="px-5 py-2 font-semibold text-center">Veces</th><th class="px-5 py-2 font-semibold text-right">Total merma</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($porProducto as $p)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 font-medium text-slate-800">{{ $p->product?->name ?? 'Producto eliminado' }}</td>
                                <td class="px-5 py-2.5 text-center text-slate-500">{{ $p->veces }}</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-rose-600">{{ number_format((float) $p->total_merma, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">No hay mermas registradas. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Por motivo --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="font-bold text-slate-800">Merma por motivo</h3>
            </div>
            <div class="overflow-x-auto max-h-80">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                        <tr><th class="px-5 py-2 font-semibold">Motivo</th><th class="px-5 py-2 font-semibold text-center">Veces</th><th class="px-5 py-2 font-semibold text-right">Total merma</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($porMotivo as $m)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 font-medium text-slate-800">{{ $m->note }}</td>
                                <td class="px-5 py-2.5 text-center text-slate-500">{{ $m->veces }}</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-rose-600">{{ number_format((float) $m->total_merma, 3) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-8 text-center text-slate-400">No hay mermas registradas. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detalle de movimientos --}}
    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800">Detalle de mermas</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left sticky top-0">
                    <tr>
                        <th class="px-5 py-2 font-semibold">Fecha</th>
                        <th class="px-5 py-2 font-semibold">Producto</th>
                        <th class="px-5 py-2 font-semibold">Cantidad</th>
                        <th class="px-5 py-2 font-semibold">Motivo</th>
                        <th class="px-5 py-2 font-semibold">Registrado por</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movimientos as $mv)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-2.5 text-slate-500">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-2.5 font-medium text-slate-800">{{ $mv->product?->name ?? 'Producto eliminado' }}</td>
                            <td class="px-5 py-2.5 font-semibold text-rose-600">{{ number_format(abs((float) $mv->quantity), 3) }}</td>
                            <td class="px-5 py-2.5 text-slate-600">{{ $mv->note }}</td>
                            <td class="px-5 py-2.5 text-slate-500">{{ $mv->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No hay mermas registradas en el período. 🎉</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-100">
            {{ $movimientos->links() }}
        </div>
    </div>
@endsection