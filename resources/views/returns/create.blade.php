@extends('layouts.app')

@section('title', 'Nueva devolución')

@section('content')
    <div class="max-w-3xl">
        <a href="{{ route('devoluciones.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a devoluciones</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">Registrar devolución</h2>

        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Paso 1: elegir venta --}}
        <form method="GET" class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5 mb-5">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">1. Selecciona la venta a devolver</label>
            <div class="flex gap-3">
                <select name="sale_id" class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand-500">
                    <option value="">Selecciona una venta...</option>
                    @foreach ($sales as $s)
                        <option value="{{ $s->id }}" {{ $sale && $sale->id === $s->id ? 'selected' : '' }}>
                            {{ $s->series }}-{{ $s->number }} · {{ $s->created_at->format('d/m/Y') }} · S/ {{ number_format($s->total, 2) }}
                        </option>
                    @endforeach
                </select>
                <button class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Cargar</button>
            </div>
        </form>

        {{-- Paso 2: elegir ítems --}}
        @if ($sale)
            <form method="POST" action="{{ route('devoluciones.store') }}">
                @csrf
                <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-800">2. Indica las cantidades a devolver</h3>
                        <p class="text-xs text-slate-400">Venta {{ $sale->series }}-{{ $sale->number }}</p>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-left">
                            <tr>
                                <th class="px-5 py-2 font-semibold">Producto</th>
                                <th class="px-5 py-2 font-semibold text-center">Precio</th>
                                <th class="px-5 py-2 font-semibold text-center">Vendido</th>
                                <th class="px-5 py-2 font-semibold text-center">Disponible a devolver</th>
                                <th class="px-5 py-2 font-semibold text-center">Devolver</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($lines as $i => $line)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-slate-800">{{ $line->item->product_name }}</td>
                                    <td class="px-5 py-3 text-center text-slate-600">S/ {{ number_format($line->item->price, 2) }}</td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ $line->item->quantity }}</td>
                                    <td class="px-5 py-3 text-center text-slate-600">{{ $line->max }}</td>
                                    <td class="px-5 py-3 text-center">
                                        <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $line->item->product_id }}">
                                        <input type="number" name="items[{{ $i }}][quantity]" value="0" min="0" max="{{ $line->max }}" {{ $line->max === 0 ? 'disabled' : '' }}
                                            class="w-20 rounded-lg border border-slate-200 px-3 py-1.5 text-sm text-center outline-none focus:border-brand-500 disabled:bg-slate-100">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="p-5 border-t border-slate-100 space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo de la devolución (opcional)</label>
                            <input type="text" name="reason" value="{{ old('reason') }}" placeholder="Producto defectuoso, error en la venta..." class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-brand-500">
                        </div>
                        <button class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-700">Registrar devolución</button>
                    </div>
                </div>
            </form>
        @endif
    </div>
@endsection
