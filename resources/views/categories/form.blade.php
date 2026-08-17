@extends('layouts.app')

@section('title', $category->exists ? 'Editar Categoría' : 'Nueva Categoría')

@section('content')
    <div class="max-w-xl">
        <a href="{{ route('categorias.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a categorías</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $category->exists ? 'Editar Categoría' : 'Nueva Categoría' }}</h2>

        @include('partials.errors')

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ $category->exists ? route('categorias.update', $category) : route('categorias.store') }}" class="space-y-4">
                @csrf
                @if ($category->exists) @method('PUT') @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                    <input type="text" name="description" value="{{ old('description', $category->description) }}"
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Categoría activa
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar</button>
                    <a href="{{ route('categorias.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
