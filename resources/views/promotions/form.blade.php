@extends('layouts.app')

@section('title', $promotion->exists ? 'Editar Promoción' : 'Nueva Promoción')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('promociones.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a promociones</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $promotion->exists ? 'Editar Promoción' : 'Nueva Promoción' }}</h2>

        @include('partials.errors')

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ $promotion->exists ? route('promociones.update', $promotion) : route('promociones.store') }}" class="space-y-4">
                @csrf
                @if ($promotion->exists) @method('PUT') @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de la promoción <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $promotion->name) }}" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Producto</label>
                        <select name="product_id" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="">— Todos los productos —</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" {{ old('product_id', $promotion->product_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo</label>
                        <select name="type" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="descuento" {{ old('type', $promotion->type ?? 'descuento') === 'descuento' ? 'selected' : '' }}>Descuento (%)</option>
                            <option value="2x1" {{ old('type', $promotion->type) === '2x1' ? 'selected' : '' }}>2x1</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descuento (%)</label>
                        <input type="number" step="0.01" min="0" max="100" name="discount_percent" value="{{ old('discount_percent', $promotion->discount_percent ?? 0) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div></div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Inicio</label>
                        <input type="date" name="starts_at" value="{{ old('starts_at', optional($promotion->starts_at)->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fin</label>
                        <input type="date" name="ends_at" value="{{ old('ends_at', optional($promotion->ends_at)->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $promotion->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Promoción activa
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar</button>
                    <a href="{{ route('promociones.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
