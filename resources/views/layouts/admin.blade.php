<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Plataforma') · SaaS Minimarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Inter',sans-serif}
        ::-webkit-scrollbar{width:6px;height:6px}
        ::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:3px}
    </style>
    <script>
        tailwind.config = { theme: { extend: { colors: { brand: { 50:'#eef2ff',100:'#e0e7ff',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca' } } } } }
    </script>
</head>
<body class="bg-slate-100/70 text-slate-800">
@php
    $nav = [
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'd' => 'M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25A2.25 2.25 0 0 1 13.5 8.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z'],
        ['label' => 'Empresas', 'route' => 'admin.tenants.index', 'd' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
        ['label' => 'Planes', 'route' => 'admin.plans.index', 'd' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
        ['label' => 'Mi Perfil', 'route' => 'admin.profile.edit', 'd' => 'M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z'],
    ];
@endphp

<div class="flex min-h-screen">
    <aside class="hidden lg:flex lg:flex-col w-64 bg-slate-900 text-slate-300 fixed inset-y-0 z-30">
        <div class="flex items-center gap-3 px-5 h-16 border-b border-slate-800">
            <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25"/></svg>
            </div>
            <div>
                <p class="font-extrabold text-white leading-tight">SaaS Minimarket</p>
                <p class="text-[11px] text-slate-400 font-medium">Plataforma</p>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            @foreach ($nav as $item)
                @php
                    $parts = explode('.', $item['route']);
                    $pattern = count($parts) >= 3 ? $parts[0] . '.' . $parts[1] . '.*' : $item['route'];
                    $active = request()->routeIs($item['route']) || request()->routeIs($pattern);
                @endphp
                <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition {{ $active ? 'bg-indigo-500/15 text-indigo-300' : 'text-slate-300 hover:bg-slate-800' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['d'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="p-3 border-t border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold text-red-400 hover:bg-slate-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
        <header class="sticky top-0 z-20 bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-6">
            <h1 class="text-lg font-bold text-slate-800">@yield('title', 'Plataforma')</h1>
            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-2 py-1 transition hover:bg-slate-100" title="Mi perfil">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()->name ?? 'S', 0, 1)) }}
                </div>
                <div class="hidden sm:block leading-tight">
                    <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400">Super Admin</p>
                </div>
            </a>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @if (session('success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
