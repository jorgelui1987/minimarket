@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('content')
    <div class="max-w-2xl space-y-6">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Mi Perfil</h2>
            <p class="text-sm text-slate-500">Administra los datos y la seguridad de tu cuenta de super administrador.</p>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Resumen de la cuenta --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-xl font-bold">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-slate-800">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
                <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700">
                    Super Administrador
                </span>
            </div>
        </div>

        {{-- Datos personales --}}
        <form method="POST" action="{{ route('admin.profile.update') }}">
            @csrf @method('PUT')
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="font-bold text-slate-800">Información de la cuenta</h3>
                    <p class="text-sm text-slate-500">Actualiza tu nombre y datos de contacto.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre completo</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div class="pt-2">
                    <button class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700">Guardar cambios</button>
                </div>
            </div>
        </form>

        {{-- Cambio de contraseña --}}
        <form method="POST" action="{{ route('admin.profile.password') }}">
            @csrf @method('PUT')
            <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="font-bold text-slate-800">Seguridad</h3>
                    <p class="text-sm text-slate-500">Usa una contraseña larga y única para proteger la plataforma.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña actual</label>
                        <input type="password" name="current_password" autocomplete="current-password" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nueva contraseña</label>
                        <input type="password" name="password" autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar nueva contraseña</label>
                        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div class="pt-2">
                    <button class="rounded-lg bg-slate-800 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-slate-900">Actualizar contraseña</button>
                </div>
            </div>
        </form>
    </div>
@endsection
