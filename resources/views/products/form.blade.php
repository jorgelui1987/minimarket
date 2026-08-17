@extends('layouts.app')

@section('title', $product->exists ? 'Editar Producto' : 'Nuevo Producto')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('productos.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a productos</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $product->exists ? 'Editar Producto' : 'Nuevo Producto' }}</h2>

        @include('partials.errors')

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ $product->exists ? route('productos.update', $product) : route('productos.store') }}" class="space-y-4">
                @csrf
                @if ($product->exists) @method('PUT') @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Código de barras</label>
                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Unidad</label>
                        <select name="unit" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            @foreach (['UND' => 'Unidad', 'KG' => 'Kilogramo', 'LT' => 'Litro', 'PQT' => 'Paquete'] as $val => $label)
                                <option value="{{ $val }}" {{ old('unit', $product->unit ?? 'UND') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Categoría</label>
                        <select name="category_id" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="">— Sin categoría —</option>
                            @foreach ($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Proveedor</label>
                        <select name="supplier_id" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            <option value="">— Sin proveedor —</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" {{ old('supplier_id', $product->supplier_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Costo (S/) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="cost" value="{{ old('cost', $product->cost ?? 0) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio venta (S/) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $product->price ?? 0) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock actual <span class="text-red-500">*</span></label>
                        <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock mínimo <span class="text-red-500">*</span></label>
                        <input type="number" min="0" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 5) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Producto activo (disponible para venta)
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar</button>
                    <a href="{{ route('productos.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
