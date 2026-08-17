@extends('layouts.app')

@section('title', 'Caja')

@section('content')
    <div class="mb-5">
        <h2 class="text-xl font-extrabold text-slate-800">Caja</h2>
        <p class="text-sm text-slate-500">Apertura, cierre y movimientos de efectivo</p>
    </div>

    @if (! $current)
        {{-- Caja cerrada: abrir --}}
        <div class="max-w-md rounded-2xl bg-white border border-slate-100 shadow-sm p-6 mb-6">
            <div class="flex items-center gap-3 mb-4">
                <span class="flex items-center justify-center w-10 h-10 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <div>
                    <h3 class="font-bold text-slate-800">No hay caja abierta</h3>
                    <p class="text-sm text-slate-500">Abre la caja para empezar a vender</p>
                </div>
            </div>
            <form method="POST" action="{{ route('caja.open') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Monto inicial en caja (S/)</label>
                    <input type="number" step="0.01" min="0" name="opening_amount" value="0" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                </div>
                <button class="w-full rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Abrir Caja</button>
            </form>
        </div>
    @else
        {{-- Caja abierta --}}
        @php
            $ingresos = $current->movements->where('type', 'ingreso')->sum('amount');
            $egresos = $current->movements->where('type', 'egreso')->sum('amount');
            $esperado = $current->opening_amount + $cashSales + $ingresos - $egresos;
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <p class="text-sm text-slate-500">Monto inicial</p>
                <p class="text-2xl font-extrabold text-slate-800">S/ {{ number_format($current->opening_amount, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Abierta {{ $current->opened_at->format('d/m H:i') }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <p class="text-sm text-slate-500">Ventas en efectivo</p>
                <p class="text-2xl font-extrabold text-emerald-600">S/ {{ number_format($cashSales, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ $salesCount }} ventas en total</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <p class="text-sm text-slate-500">Ingresos / Egresos</p>
                <p class="text-2xl font-extrabold text-slate-800">+{{ number_format($ingresos, 2) }} / -{{ number_format($egresos, 2) }}</p>
                <p class="text-xs text-slate-400 mt-1">Movimientos manuales</p>
            </div>
            <div class="rounded-2xl bg-gradient-to-br from-brand-500 to-emerald-600 text-white shadow-sm p-5">
                <p class="text-sm text-white/80">Efectivo esperado</p>
                <p class="text-2xl font-extrabold">S/ {{ number_format($esperado, 2) }}</p>
                <p class="text-xs text-white/70 mt-1">Inicial + ventas + mov.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            {{-- Movimientos --}}
            <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                <h3 class="font-bold text-slate-800 mb-4">Movimientos de la caja</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-left">
                            <tr>
                                <th class="px-4 py-2 font-semibold">Hora</th>
                                <th class="px-4 py-2 font-semibold">Concepto</th>
                                <th class="px-4 py-2 font-semibold">Tipo</th>
                                <th class="px-4 py-2 font-semibold text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($current->movements->sortByDesc('created_at') as $m)
                                <tr>
                                    <td class="px-4 py-2 text-slate-500">{{ $m->created_at->format('H:i') }}</td>
                                    <td class="px-4 py-2 text-slate-800">{{ $m->concept }}</td>
                                    <td class="px-4 py-2">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $m->type === 'ingreso' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">{{ ucfirst($m->type) }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-right font-medium {{ $m->type === 'ingreso' ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $m->type === 'ingreso' ? '+' : '-' }}S/ {{ number_format($m->amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Sin movimientos manuales.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="space-y-4">
                <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
                    <h3 class="font-bold text-slate-800 mb-3">Registrar movimiento</h3>
                    <form method="POST" action="{{ route('caja.movement') }}" class="space-y-3">
                        @csrf
                        <select name="type" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                            <option value="ingreso">Ingreso</option>
                            <option value="egreso">Egreso</option>
                        </select>
                        <input type="text" name="concept" placeholder="Concepto" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <input type="number" step="0.01" min="0.01" name="amount" placeholder="Monto" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <button class="w-full rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Registrar</button>
                    </form>
                </div>

                <div class="rounded-2xl bg-white border border-red-100 shadow-sm p-5">
                    <h3 class="font-bold text-slate-800 mb-3">Cerrar caja</h3>
                    <form method="POST" action="{{ route('caja.close') }}" class="space-y-3" onsubmit="return confirm('¿Cerrar la caja? Esta acción no se puede deshacer.')">
                        @csrf
                        <input type="number" step="0.01" min="0" name="closing_amount" placeholder="Efectivo contado (S/)" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <input type="text" name="notes" placeholder="Observaciones (opcional)" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
                        <button class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Cerrar Caja</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Historial --}}
    <div class="mt-6 rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-800">Historial de cierres</h3></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Cerrada</th>
                        <th class="px-5 py-3 font-semibold">Usuario</th>
                        <th class="px-5 py-3 font-semibold text-right">Inicial</th>
                        <th class="px-5 py-3 font-semibold text-right">Esperado</th>
                        <th class="px-5 py-3 font-semibold text-right">Contado</th>
                        <th class="px-5 py-3 font-semibold text-right">Diferencia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($history as $h)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-500">{{ $h->closed_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-3 text-slate-800">{{ $h->user?->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($h->opening_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right text-slate-500">S/ {{ number_format($h->expected_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right font-medium text-slate-800">S/ {{ number_format($h->closing_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right font-semibold {{ $h->difference == 0 ? 'text-slate-500' : ($h->difference > 0 ? 'text-emerald-600' : 'text-red-600') }}">
                                S/ {{ number_format($h->difference, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">Aún no hay cierres registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $history->links() }}</div>
    </div>
@endsection
