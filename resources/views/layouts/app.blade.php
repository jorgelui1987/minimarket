<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de Control') · {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif}
        ::-webkit-scrollbar{width:6px;height:6px}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}
        [x-cloak]{display:none}
    </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50:'#ecfdf5',100:'#d1fae5',400:'#34d399',500:'#10b981',600:'#059669',700:'#047857' } } } } }
    </script>
</head>
<body class="bg-slate-50 text-slate-800">
@php
    $sections = [
        ['title' => null, 'items' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'grid'],
            ['label' => 'Punto de Venta', 'route' => 'pos', 'icon' => 'cart'],
        ]],
        ['title' => 'Operaciones', 'items' => [
            ['label' => 'Ventas', 'route' => 'ventas.index', 'icon' => 'receipt'],
            ['label' => 'Devoluciones', 'route' => 'devoluciones.index', 'icon' => 'return'],
            ['label' => 'Compras', 'route' => 'compras.index', 'icon' => 'truck'],
            ['label' => 'Caja', 'route' => 'caja.index', 'icon' => 'cash'],
        ]],
        ['title' => 'Inventario', 'items' => [
            ['label' => 'Productos', 'route' => 'productos.index', 'icon' => 'box'],
            ['label' => 'Control de Stock', 'route' => 'inventario.index', 'icon' => 'layers'],
            ['label' => 'Categorías', 'route' => 'categorias.index', 'icon' => 'tag'],
            ['label' => 'Promociones', 'route' => 'promociones.index', 'icon' => 'percent'],
        ]],
        ['title' => 'Contactos', 'items' => [
            ['label' => 'Clientes', 'route' => 'clientes.index', 'icon' => 'users'],
            ['label' => 'Proveedores', 'route' => 'proveedores.index', 'icon' => 'building'],
        ]],
        ['title' => 'Análisis', 'items' => [
            ['label' => 'Reportes', 'route' => 'reportes', 'icon' => 'chart'],
        ]],
        ['title' => 'Sistema', 'items' => [
            ['label' => 'Mi Perfil', 'route' => 'perfil.edit', 'icon' => 'user'],
            ['label' => 'Usuarios', 'route' => 'usuarios.index', 'icon' => 'user-cog'],
            ['label' => 'Mi Suscripción', 'route' => 'suscripcion', 'icon' => 'shield'],
            ['label' => 'Configuración', 'route' => 'configuracion.edit', 'icon' => 'cog'],
            ['label' => 'Facturación Electrónica', 'route' => 'facturacion.config.edit', 'icon' => 'doc'],
            ['label' => 'Backup', 'route' => 'backup', 'icon' => 'database'],
        ]],
    ];
    $icons = [
        'grid' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z',
        'cart' => 'M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
        'receipt' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z',
        'truck' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-6.75M18.75 7.5h-7.5v6.75h7.5V7.5Z',
        'cash' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'box' => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'tag' => 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z M6 6h.008v.008H6V6Z',
        'percent' => 'M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185ZM9.75 9h.008v.008H9.75V9Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008V13.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z',
        'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
        'building' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21',
        'doc' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        'calendar' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
        'chart' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
        'user-cog' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
        'cog' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
        'shield' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'database' => 'M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125',
        'layers' => 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122',
        'return' => 'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3',
        'user' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
    ];
@endphp

<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="hidden lg:flex lg:flex-col w-64 bg-gradient-to-b from-emerald-800 to-emerald-950 text-emerald-50 fixed inset-y-0 z-30">
        <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/15 backdrop-blur">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.836l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272"/></svg>
            </div>
            <div>
                <p class="font-extrabold text-white leading-tight">{{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }}</p>
                <p class="text-[11px] text-emerald-200/70 font-medium">Sistema POS</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-4">
            @foreach ($sections as $section)
                <div>
                    @if ($section['title'])
                        <p class="px-3 mb-1.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300/60">{{ $section['title'] }}</p>
                    @endif
                    <div class="space-y-0.5">
                        @foreach ($section['items'] as $item)
                            @php
                                $base = explode('.', $item['route'])[0];
                                $active = request()->routeIs($item['route']) || request()->routeIs($base.'.*');
                            @endphp
                            <a href="{{ route($item['route']) }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                               {{ $active ? 'bg-white/15 text-white shadow-sm' : 'text-emerald-100/80 hover:bg-white/10 hover:text-white' }}">
                                <svg class="w-5 h-5 shrink-0 {{ $active ? 'text-white' : 'text-emerald-300' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$item['icon']] }}"/></svg>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold text-rose-200 hover:bg-white/10 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <h1 class="text-lg font-bold text-slate-800">@yield('title', 'Panel de Control')</h1>
            </div>
            <div class="flex items-center gap-4">
                <span class="hidden sm:inline-flex items-center gap-2 text-sm text-slate-500">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, DD [de] MMMM [de] YYYY') }}
                </span>
                <a href="{{ route('perfil.edit') }}" class="flex items-center gap-2.5 pl-4 border-l border-slate-200 rounded-lg py-1 pr-2 transition hover:bg-slate-50" title="Mi perfil">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-emerald-600 flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden sm:block leading-tight">
                        <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()->roleLabel() ?? '' }}</p>
                    </div>
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @php $tnt = auth()->user()?->tenant; $tntDays = $tnt?->daysLeft(); @endphp
            @if ($tnt && ! $tnt->isExpired() && ($tnt->status === 'trial' || (! is_null($tntDays) && $tntDays <= 7)))
                <div class="mb-5 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <span>
                        @if ($tnt->status === 'trial')
                            🎁 Estás en el plan de prueba — te quedan <strong>{{ $tntDays }} días</strong>.
                        @else
                            ⏳ Tu suscripción vence en <strong>{{ $tntDays }} días</strong>.
                        @endif
                    </span>
                    <a href="{{ route('suscripcion') }}" class="font-semibold underline">Ver mi suscripción</a>
                </div>
            @endif
            @if (session('success'))
                <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
