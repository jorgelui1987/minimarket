@extends('layouts.app')

@section('title', 'Panel de Control')

@php
    $fmt = fn ($n) => 'S/ ' . number_format($n, 2);
@endphp

@section('content')
    {{-- Banner bienvenida --}}
    <div class="mb-5 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/></svg>
        <p class="text-sm font-medium text-emerald-800">¡Bienvenido {{ auth()->user()->name }}!</p>
    </div>

    <div class="mb-6">
        <h2 class="text-2xl font-extrabold text-slate-800">¡Hola, {{ explode(' ', auth()->user()->name)[0] }}! 👋</h2>
        <p class="text-slate-500">Resumen de tu negocio · {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, DD [de] MMMM') }}</p>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['Ventas del día', $fmt($ventasHoy), $ticketsHoy.' tickets', 'HOY', 'from-emerald-400 to-emerald-600', 'cash'],
                ['Ventas del mes', $fmt($ventasMes), $ticketsMes.' tickets', 'MES', 'from-blue-400 to-blue-600', 'chart'],
                ['Productos', (string) $totalProductos, $stockBajo.' con stock bajo', 'STOCK', 'from-amber-400 to-orange-500', 'box'],
                ['Clientes', (string) $totalClientes, $porVencer.' registrados', 'ACTIVOS', 'from-violet-400 to-purple-600', 'users'],
            ];
            $cardIcons = [
                'cash' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z',
                'chart' => 'M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941',
                'box' => 'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
                'users' => 'M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z',
            ];
        @endphp
        @foreach ($cards as $c)
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $c[4] }} p-5 text-white shadow-lg">
                <div class="flex items-start justify-between">
                    <div class="rounded-lg bg-white/20 p-2">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $cardIcons[$c[5]] }}"/></svg>
                    </div>
                    <span class="rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold tracking-wide">{{ $c[3] }}</span>
                </div>
                <p class="mt-4 text-sm font-medium text-white/80">{{ $c[0] }}</p>
                <p class="text-3xl font-extrabold">{{ $c[1] }}</p>
                <p class="mt-1 text-xs text-white/70">{{ $c[2] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Fila 1 de gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800">Ventas · Últimos 7 días</h3>
                    <p class="text-xs text-slate-400">Evolución de las ventas diarias</p>
                </div>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-600">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> En tiempo real
                </span>
            </div>
            <div class="h-64"><canvas id="chartLinea"></canvas></div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-4">Ventas por Categoría</h3>
            <div class="h-44"><canvas id="chartCategoria"></canvas></div>
            <div class="mt-4 space-y-1.5">
                @foreach ($porCategoria as $i => $cat)
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-slate-600">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ ['#10b981','#a855f7','#f59e0b','#ef4444','#3b82f6','#14b8a6'][$i] ?? '#94a3b8' }}"></span>
                            {{ $cat->name }}
                        </span>
                        <span class="font-semibold text-slate-700">{{ $fmt($cat->total) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Fila 2 de gráficos --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800">Ventas por Día de la Semana</h3>
                    <p class="text-xs text-slate-400">Últimos 30 días · identifica tus días más fuertes</p>
                </div>
            </div>
            <div class="h-64"><canvas id="chartDia"></canvas></div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 p-5 shadow-sm">
            <h3 class="font-bold text-slate-800 mb-4">Forma de Pago</h3>
            <div class="h-44"><canvas id="chartPago"></canvas></div>
            <div class="mt-4 space-y-1.5">
                @foreach ($formaPago as $i => $fp)
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2 text-slate-600">
                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ ['#10b981','#3b82f6','#a855f7','#f59e0b'][$i] ?? '#94a3b8' }}"></span>
                            {{ ucfirst($fp->payment_method) }}
                        </span>
                        <span class="font-semibold text-slate-700">{{ $fmt($fp->total) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
        const money = (v) => 'S/ ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2 });
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';

        // Línea
        new Chart(document.getElementById('chartLinea'), {
            type: 'line',
            data: {
                labels: @json($linea->pluck('label')),
                datasets: [{
                    data: @json($linea->pluck('total')),
                    borderColor: '#10b981', borderWidth: 3, tension: 0.4, fill: true,
                    backgroundColor: (ctx) => { const g = ctx.chart.ctx.createLinearGradient(0,0,0,260); g.addColorStop(0,'rgba(16,185,129,0.25)'); g.addColorStop(1,'rgba(16,185,129,0)'); return g; },
                    pointBackgroundColor: '#10b981', pointRadius: 4,
                }]
            },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => money(c.parsed.y) } } },
                scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'S/' + v }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        // Donut categoría
        new Chart(document.getElementById('chartCategoria'), {
            type: 'doughnut',
            data: { labels: @json($porCategoria->pluck('name')),
                datasets: [{ data: @json($porCategoria->pluck('total')), backgroundColor: ['#10b981','#a855f7','#f59e0b','#ef4444','#3b82f6','#14b8a6'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => c.label + ': ' + money(c.parsed) } } } }
        });

        // Barras día
        new Chart(document.getElementById('chartDia'), {
            type: 'bar',
            data: { labels: @json($dias),
                datasets: [{ data: @json($porDia), borderRadius: 8,
                    backgroundColor: (ctx) => { const g = ctx.chart.ctx.createLinearGradient(0,0,0,260); g.addColorStop(0,'#c084fc'); g.addColorStop(1,'#e9d5ff'); return g; } }] },
            options: { responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => money(c.parsed.y) } } },
                scales: { y: { beginAtZero: true, ticks: { callback: (v) => 'S/' + v }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } }
        });

        // Donut pago
        new Chart(document.getElementById('chartPago'), {
            type: 'doughnut',
            data: { labels: @json($formaPago->pluck('payment_method')),
                datasets: [{ data: @json($formaPago->pluck('total')), backgroundColor: ['#10b981','#3b82f6','#a855f7','#f59e0b'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '65%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => c.label + ': ' + money(c.parsed) } } } }
        });
    </script>
@endsection
