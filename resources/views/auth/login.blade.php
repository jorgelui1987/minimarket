<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif}</style>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50:'#ecfdf5',100:'#d1fae5',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857' } } } } }
    </script>
</head>
<body class="min-h-screen flex">
    {{-- ===== Panel izquierdo (marca) ===== --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 text-white">
        <div class="absolute -top-24 -left-24 w-96 h-96 rounded-full bg-white/10 blur-2xl"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full bg-teal-400/20 blur-3xl"></div>

        <div class="relative z-10 flex flex-col justify-center px-14 xl:px-20 w-full">
            <div class="flex flex-col items-start">
                <div class="flex items-center justify-center w-20 h-20 rounded-3xl bg-white/15 backdrop-blur shadow-xl mb-5">
                    <svg class="w-11 h-11 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight">{{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }}</h1>
                <p class="mt-1 text-sm font-semibold tracking-[0.2em] text-emerald-200 uppercase">Sistema de Punto de Venta</p>
                <span class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/15 border border-white/25 px-4 py-1.5 text-sm font-semibold text-white backdrop-blur">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 0 0 1.332-7.257 3 3 0 0 0-3.758-3.848 5.25 5.25 0 0 0-10.233 2.33A4.502 4.502 0 0 0 2.25 15Z"/></svg>
                    Aplicación SaaS · Multi-empresa
                </span>
            </div>

            <div class="mt-12 space-y-6 max-w-md">
                @php
                    $features = [
                        ['Punto de Venta Ágil', 'Cobra rápido con descuento de stock e IGV automático', 'M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z'],
                        ['Inventario y Kardex', 'Controla entradas, salidas, alertas y ajustes de stock', 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878M19.5 9.878a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122'],
                        ['Reportes en Tiempo Real', 'Métricas, gráficos y exportación a Excel y PDF', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z'],
                        ['Multi-dispositivo', 'Accede desde cualquier equipo de tu negocio', 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3'],
                    ];
                @endphp
                @foreach ($features as [$title, $desc, $d])
                    <div class="flex items-start gap-4">
                        <div class="flex items-center justify-center w-11 h-11 shrink-0 rounded-xl bg-white/15 backdrop-blur">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold">{{ $title }}</p>
                            <p class="text-sm text-emerald-100/80">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 grid grid-cols-3 gap-3 max-w-md">
                @foreach ([['IGV', 'Incluido'], ['100%', 'Local y seguro'], ['24/7', 'Disponible']] as [$num, $lbl])
                    <div class="rounded-2xl bg-white/10 backdrop-blur px-4 py-4 text-center">
                        <p class="text-2xl font-extrabold">{{ $num }}</p>
                        <p class="text-xs text-emerald-100/80 mt-0.5">{{ $lbl }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== Panel derecho (formulario) ===== --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center bg-white p-6 sm:p-10">
        <div class="w-full max-w-sm">
            {{-- Logo compacto solo en móvil --}}
            <div class="lg:hidden flex flex-col items-center mb-8">
                <div class="flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-emerald-600 shadow-lg mb-3">
                    <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                </div>
                <h1 class="text-xl font-extrabold text-slate-800">{{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }}</h1>
            </div>

            <h2 class="text-2xl font-extrabold text-slate-800">Bienvenido de vuelta 👋</h2>
            <p class="text-sm text-slate-500 mt-1 mb-6">Ingresa tus credenciales para acceder al sistema</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo electrónico</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full rounded-lg border border-slate-200 pl-10 pr-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition"
                            placeholder="admin@minimarket.test">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                        </span>
                        <input type="password" name="password" required
                            class="w-full rounded-lg border border-slate-200 pl-10 pr-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-100 outline-none transition"
                            placeholder="••••••••">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        Recordarme
                    </label>
                    <a href="{{ route('register') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700">Crear cuenta</a>
                </div>
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-brand-500 to-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 hover:from-brand-600 hover:to-emerald-700 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H2.25"/></svg>
                    Iniciar Sesión
                </button>
            </form>

            {{-- Cuentas demo --}}
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center"><span class="bg-white px-3 text-xs text-slate-400">Cuentas de demostración</span></div>
            </div>

            <div class="rounded-xl border border-slate-200 divide-y divide-slate-100 overflow-hidden">
                <button type="button" onclick="fillLogin('admin@minimarket.test')" class="w-full flex items-center justify-between px-4 py-2.5 text-sm hover:bg-slate-50 transition">
                    <span class="text-slate-700">admin@minimarket.test</span>
                    <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-xs font-semibold text-brand-700">Admin</span>
                </button>
                <button type="button" onclick="fillLogin('cajero@minimarket.test')" class="w-full flex items-center justify-between px-4 py-2.5 text-sm hover:bg-slate-50 transition">
                    <span class="text-slate-700">cajero@minimarket.test</span>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-semibold text-blue-700">Cajero</span>
                </button>
            </div>
            <p class="text-center text-xs text-slate-400 mt-3">Contraseña para ambas: <span class="font-semibold text-slate-500">password</span></p>

            <p class="text-center text-xs text-slate-400 mt-8">© {{ date('Y') }} {{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }} · Todos los derechos reservados</p>
        </div>
    </div>

    <script>
        function fillLogin(email) {
            document.querySelector('input[name=email]').value = email;
            document.querySelector('input[name=password]').value = 'password';
            document.querySelector('input[name=password]').focus();
        }
    </script>
</body>
</html>
