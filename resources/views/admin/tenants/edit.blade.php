@extends('layouts.admin')

@section('title', 'Gestionar Empresa')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('admin.tenants.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a empresas</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-1">{{ $tenant->name }}</h2>
        <p class="text-sm text-slate-500 mb-5">{{ $tenant->users_count }} usuario(s) · creada el {{ $tenant->created_at->format('d/m/Y') }}</p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.tenants.update', $tenant) }}">
            @csrf @method('PUT')
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de la empresa</label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">RUC</label>
                        <input type="text" name="ruc" value="{{ old('ruc', $tenant->ruc) }}" maxlength="11" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Plan</label>
                        <select name="plan_id" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                            @foreach ($plans as $p)
                                <option value="{{ $p->id }}" {{ old('plan_id', $tenant->plan_id) == $p->id ? 'selected' : '' }}>{{ $p->name }} — S/ {{ number_format($p->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
                        <select name="status" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                            @foreach (['trial' => 'En prueba', 'active' => 'Activa', 'suspended' => 'Suspendida'] as $v => $l)
                                <option value="{{ $v }}" {{ old('status', $tenant->status) === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo de contacto</label>
                        <input type="email" name="contact_email" value="{{ old('contact_email', $tenant->contact_email) }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', $tenant->contact_phone) }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fin de prueba</label>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', optional($tenant->trial_ends_at)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fin de suscripción</label>
                        <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', optional($tenant->subscription_ends_at)->format('Y-m-d')) }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Empresa habilitada (puede acceder al sistema)
                </label>

                <div class="flex gap-3 pt-2">
                    <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">Guardar cambios</button>
                    <a href="{{ route('admin.tenants.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
@endsection
