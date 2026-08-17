@extends('layouts.app')

@section('title', $customer->exists ? 'Editar Cliente' : 'Nuevo Cliente')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a clientes</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $customer->exists ? 'Editar Cliente' : 'Nuevo Cliente' }}</h2>

        @include('partials.errors')

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ $customer->exists ? route('clientes.update', $customer) : route('clientes.store') }}" class="space-y-4">
                @csrf
                @if ($customer->exists) @method('PUT') @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre / Razón social <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de documento</label>
                        <select name="doc_type" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            @foreach (['DNI', 'RUC', 'CE', 'Pasaporte'] as $dt)
                                <option value="{{ $dt }}" {{ old('doc_type', $customer->doc_type ?? 'DNI') === $dt ? 'selected' : '' }}>{{ $dt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">N° de documento</label>
                        <input type="text" name="doc_number" value="{{ old('doc_number', $customer->doc_number) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                        <input type="text" name="address" value="{{ old('address', $customer->address) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cumpleaños</label>
                        <input type="date" name="birthday" value="{{ old('birthday', optional($customer->birthday)->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Cliente activo
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar</button>
                    <a href="{{ route('clientes.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
