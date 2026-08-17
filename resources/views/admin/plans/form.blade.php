@extends('layouts.admin')

@section('title', $plan->exists ? 'Editar Plan' : 'Nuevo Plan')

@section('content')
    <div class="max-w-xl">
        <a href="{{ route('admin.plans.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a planes</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $plan->exists ? 'Editar Plan' : 'Nuevo Plan' }}</h2>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}">
            @csrf
            @if ($plan->exists) @method('PUT') @endif
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del plan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $plan->name) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio mensual (S/) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $plan->price ?? 0) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                </div>
                <p class="text-xs text-slate-400">Usa <strong>-1</strong> para indicar "ilimitado" en cualquiera de los límites.</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Máx. productos</label>
                        <input type="number" min="-1" name="max_products" value="{{ old('max_products', $plan->max_products ?? -1) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Máx. usuarios</label>
                        <input type="number" min="-1" name="max_users" value="{{ old('max_users', $plan->max_users ?? -1) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Máx. ventas/mes</label>
                        <input type="number" min="-1" name="max_monthly_sales" value="{{ old('max_monthly_sales', $plan->max_monthly_sales ?? -1) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Plan activo (disponible para asignar)
                </label>
                <div class="flex gap-3 pt-2">
                    <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">Guardar</button>
                    <a href="{{ route('admin.plans.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
@endsection
