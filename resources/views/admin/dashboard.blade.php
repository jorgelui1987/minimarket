@extends('layouts.admin')

@section('title', 'Dashboard de Plataforma')

@section('content')
    {{-- ===== KPI cards ===== --}}
    <div class="grid grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">Empresas</p>
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/></svg>
                </span>
            </div>
            <p class="mt-2 text-3xl font-extrabold text-slate-800">{{ $total }}</p>
            <p class="text-xs text-slate-400">{{ $totalUsuarios }} usuarios en total</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white p-5 shadow-lg shadow-emerald-500/20">
            <p class="text-sm text-white/80 font-medium">Activas</p>
            <p class="mt-2 text-3xl font-extrabold">{{ $activas }}</p>
            <p class="text-xs text-white/70">{{ $total ? round($activas / $total * 100) : 0 }}% del total</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white p-5 shadow-lg shadow-blue-500/20">
            <p class="text-sm text-white/80 font-medium">En prueba</p>
            <p class="mt-2 text-3xl font-extrabold">{{ $enPrueba }}</p>
            <p class="text-xs text-white/70">conversión potencial</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-red-400 to-rose-600 text-white p-5 shadow-lg shadow-rose-500/20">
            <p class="text-sm text-white/80 font-medium">Vencidas</p>
            <p class="mt-2 text-3xl font-extrabold">{{ $vencidas }}</p>
            <p class="text-xs text-white/70">requieren atención</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-violet-400 to-purple-600 text-white p-5 shadow-lg shadow-purple-500/20 col-span-2 xl:col-span-1">
            <p class="text-sm text-white/80 font-medium">MRR estimado</p>
            <p class="mt-2 text-3xl font-extrabold">S/ {{ number_format($mrr, 2) }}</p>
            <p class="text-xs text-white/70">ingreso mensual recurrente</p>
        </div>
    </div>

    {{-- ===== Gráficos ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800">Crecimiento de empresas</h3>
            <p class="text-xs text-slate-400 mb-3">Altas por mes — últimos 6 meses</p>
            <div class="h-64"><canvas id="growthChart"></canvas></div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800">Ingresos (MRR) por plan</h3>
            <p class="text-xs text-slate-400 mb-3">Aporte de cada plan en S/ / mes</p>
            <div class="h-64"><canvas id="mrrChart"></canvas></div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800">Empresas por plan</h3>
            <p class="text-xs text-slate-400 mb-3">Distribución de empresas según su plan</p>
            <div class="h-64"><canvas id="planChart"></canvas></div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800">Estado de suscripciones</h3>
            <p class="text-xs text-slate-400 mb-3">Activas, en prueba y vencidas</p>
            <div class="h-64"><canvas id="estadoChart"></canvas></div>
        </div>
    </div>

    {{-- ===== Empresas recientes ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800">Empresas recientes</h3>
                <a href="{{ route('admin.tenants.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">Ver todas</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-left">
                        <tr><th class="px-5 py-2 font-semibold">Empresa</th><th class="px-5 py-2 font-semibold">Plan</th><th class="px-5 py-2 font-semibold text-center">Usuarios</th><th class="px-5 py-2 font-semibold text-center">Estado</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recientes as $t)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-xs font-bold">{{ strtoupper(substr($t->name, 0, 1)) }}</span>
                                        <span class="font-medium text-slate-800">{{ $t->name }}</span>
                                    </div>
                                </td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $t->plan?->name ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-center text-slate-600">{{ $t->users_count }}</td>
                                <td class="px-5 py-2.5 text-center">
                                    @php $exp = $t->isExpired(); @endphp
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $exp ? 'bg-red-50 text-red-600' : ($t->status === 'trial' ? 'bg-blue-50 text-blue-700' : 'bg-emerald-50 text-emerald-700') }}">{{ $t->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Aún no hay empresas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-4">Empresas por plan</h3>
            <div class="space-y-3">
                @foreach ($planes as $p)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-slate-800">{{ $p->name }}</p>
                            <p class="text-xs text-slate-400">S/ {{ number_format($p->price, 2) }}/mes</p>
                        </div>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">{{ $p->tenants_count }}</span>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.plans.index') }}" class="mt-4 block text-center text-sm font-semibold text-indigo-600 hover:text-indigo-700">Gestionar planes</a>
        </div>
    </div>

    {{-- ===== Scripts de los gráficos ===== --}}
    <script>
        const palette = ['#6366f1', '#10b981', '#3b82f6', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6'];
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#64748b';
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.boxWidth = 8;

        // Crecimiento de empresas (línea)
        new Chart(document.getElementById('growthChart'), {
            type: 'line',
            data: {
                labels: @json($growthLabels),
                datasets: [{
                    label: 'Nuevas empresas',
                    data: @json($growthData),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.12)',
                    fill: true, tension: 0.35, borderWidth: 2,
                    pointBackgroundColor: '#6366f1', pointRadius: 4,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });

        // MRR por plan (barras)
        new Chart(document.getElementById('mrrChart'), {
            type: 'bar',
            data: {
                labels: @json($chartPlanLabels),
                datasets: [{
                    label: 'MRR (S/)',
                    data: @json($mrrPorPlan),
                    backgroundColor: palette.map(c => c + 'cc'),
                    borderRadius: 8, maxBarThickness: 56,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => 'S/ ' + Number(c.raw).toFixed(2) } } },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { callback: v => 'S/ ' + v } }, x: { grid: { display: false } } },
            },
        });

        // Empresas por plan (dona)
        new Chart(document.getElementById('planChart'), {
            type: 'doughnut',
            data: {
                labels: @json($chartPlanLabels),
                datasets: [{ data: @json($chartPlanData), backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }],
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } },
        });

        // Estado de suscripciones (dona)
        new Chart(document.getElementById('estadoChart'), {
            type: 'doughnut',
            data: {
                labels: ['Activas', 'En prueba', 'Vencidas'],
                datasets: [{ data: @json($estadoData), backgroundColor: ['#10b981', '#3b82f6', '#ef4444'], borderWidth: 2, borderColor: '#fff' }],
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' } } },
        });
    </script>
@endsection
