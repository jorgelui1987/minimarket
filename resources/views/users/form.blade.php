@extends('layouts.app')

@section('title', $user->exists ? 'Editar Usuario' : 'Nuevo Usuario')

@section('content')
    <div class="max-w-2xl">
        <a href="{{ route('usuarios.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">← Volver a usuarios</a>
        <h2 class="text-xl font-extrabold text-slate-800 mt-2 mb-5">{{ $user->exists ? 'Editar Usuario' : 'Nuevo Usuario' }}</h2>

        @include('partials.errors')

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ $user->exists ? route('usuarios.update', $user) : route('usuarios.store') }}" class="space-y-4">
                @csrf
                @if ($user->exists) @method('PUT') @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Rol <span class="text-red-500">*</span></label>
                        <select name="role" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                            @foreach (['admin' => 'Administrador', 'cajero' => 'Cajero', 'almacen' => 'Almacén'] as $val => $label)
                                <option value="{{ $val }}" {{ old('role', $user->role ?? 'cajero') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Contraseña {{ $user->exists ? '(dejar en blanco para no cambiar)' : '*' }}
                        </label>
                        <input type="password" name="password" {{ $user->exists ? '' : 'required' }}
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none">
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                    Usuario activo
                </label>

                <div class="flex gap-3 pt-2">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:from-brand-600 hover:to-emerald-700">Guardar</button>
                    <a href="{{ route('usuarios.index') }}" class="rounded-lg border border-slate-200 px-5 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
@endsection
