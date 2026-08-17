<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Cuenta · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50:'#ecfdf5',100:'#d1fae5',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857' } } } } }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-teal-50 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-brand-500 to-emerald-600 shadow-lg shadow-emerald-200 mb-4">
                <svg class="w-9 h-9 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-800">Mi Minimarket</h1>
            <p class="text-slate-500 text-sm mt-1">Sistema de Punto de Venta</p>
        </div>

        <div class="bg-white rounded-2xl shadow-xl shadow-slate-200/60 border border-slate-100 p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-1">Crea tu empresa</h2>
            <p class="text-sm text-slate-500 mb-6">Empieza con 14 días de prueba gratis</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del negocio</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" required autofocus
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none"
                        placeholder="Mi Minimarket S.A.C.">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tu nombre completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                    <input type="password" name="password" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition">
                </div>
                <button type="submit"
                    class="w-full rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:from-brand-600 hover:to-emerald-700 transition">
                    Crear Cuenta
                </button>
            </form>

            <p class="text-center text-sm text-slate-500 mt-6">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700">Inicia sesión</a>
            </p>
        </div>
    </div>
</body>
</html>
