@extends('layouts.app')

@section('title', 'Reportes')

@php $cur = \App\Models\Setting::get('currency', 'S/'); @endphp

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <h2 class="text-xl font-extrabold text-slate-800">Reportes</h2>
            <p class="text-sm text-slate-500">Análisis del {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</p>
        </div>
        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-brand-500">
            </div>
            <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Aplicar</button>
        </form>
    </div>

    <div class="flex flex-wrap gap-2 mb-5">
        <a href="{{ route('reportes.export', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-100">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Exportar a Excel
        </a>
        <a href="{{ route('reportes.print', ['from' => $from->format('Y-m-d'), 'to' => $to->format('Y-m-d')]) }}" target="_blank"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
            Imprimir / PDF
        </a>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white p-5 shadow-lg">
            <p class="text-sm text-white/80">Ventas totales</p>
            <p class="text-2xl font-extrabold">{{ $cur }} {{ number_format($totalVentas, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 text-white p-5 shadow-lg">
            <p class="text-sm text-white/80">N° de ventas</p>
            <p class="text-2xl font-extrabold">{{ $numVentas }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-violet-400 to-purple-600 text-white p-5 shadow-lg">
            <p class="text-sm text-white/80">Ticket promedio</p>
            <p class="text-2xl font-extrabold">{{ $cur }} {{ number_format($ticketPromedio, 2) }}</p>
        </div>
        <div class="rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white p-5 shadow-lg">
            <p class="text-sm text-white/80">Utilidad estimada</p>
            <p class="text-2xl font-extrabold">{{ $cur }} {{ number_format($utilidad, 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Top productos --}}
        <div class="lg:col-span-2 rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-4">Productos más vendidos</h3>
            @if ($topProductos->isEmpty())
                <p class="text-center text-slate-400 py-10">Sin datos en este periodo.</p>
            @else
                <div class="h-72"><canvas id="chartTop"></canvas></div>
            @endif
        </div>

        {{-- Por categoría --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-4">Ventas por categoría</h3>
            @if ($porCategoria->isEmpty())
                <p class="text-center text-slate-400 py-10">Sin datos.</p>
            @else
                <div class="h-52"><canvas id="chartCat"></canvas></div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Por usuario --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100"><h3 class="font-bold text-slate-800">Ventas por cajero</h3></div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-left">
                    <tr><th class="px-5 py-2 font-semibold">Cajero</th><th class="px-5 py-2 font-semibold text-center">Ventas</th><th class="px-5 py-2 font-semibold text-right">Total</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($porUsuario as $u)
                        <tr>
                            <td class="px-5 py-2 text-slate-800">{{ $u->name ?? '—' }}</td>
                            <td class="px-5 py-2 text-center text-slate-600">{{ $u->num }}</td>
                            <td class="px-5 py-2 text-right font-medium text-slate-800">{{ $cur }} {{ number_format($u->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Sin datos.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Por método de pago --}}
        <div class="rounded-2xl bg-white border border-slate-100 shadow-sm p-5">
            <h3 class="font-bold text-slate-800 mb-4">Por método de pago</h3>
            @if ($porPago->isEmpty())
                <p class="text-center text-slate-400 py-10">Sin datos.</p>
            @else
                <div class="grid grid-cols-2 gap-4 items-center">
                    <div class="h-44"><canvas id="chartPago"></canvas></div>
                    <div class="space-y-2">
                        @foreach ($porPago as $i => $pp)
                            <div class="flex items-center justify-between text-sm">
                                <span class="flex items-center gap-2 text-slate-600">
                                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ ['#10b981','#3b82f6','#a855f7','#f59e0b'][$i] ?? '#94a3b8' }}"></span>
                                    {{ ucfirst($pp->payment_method) }}
                                </span>
                                <span class="font-semibold text-slate-700">{{ $cur }} {{ number_format($pp->total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Stock bajo --}}
    <div class="rounded-2xl bg-white border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            <h3 class="font-bold text-slate-800">Productos con stock bajo</h3>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-left">
                <tr><th class="px-5 py-2 font-semibold">Producto</th><th class="px-5 py-2 font-semibold">Categoría</th><th class="px-5 py-2 font-semibold text-center">Stock</th><th class="px-5 py-2 font-semibold text-center">Mínimo</th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($stockBajo as $p)
                    <tr>
                        <td class="px-5 py-2 text-slate-800">{{ $p->name }}</td>
                        <td class="px-5 py-2 text-slate-500">{{ $p->category?->name ?? '—' }}</td>
                        <td class="px-5 py-2 text-center"><span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600">{{ $p->stock }}</span></td>
                        <td class="px-5 py-2 text-center text-slate-500">{{ $p->min_stock }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-5 py-6 text-center text-slate-400">¡Todo el stock está en niveles correctos!</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <script>
        Chart.defaults.font.family = 'Inter, sans-serif';
        Chart.defaults.color = '#94a3b8';
        const cur = @json($cur);
        const money = (v) => cur + ' ' + Number(v).toLocaleString('es-PE', { minimumFractionDigits: 2 });

        @if ($topProductos->isNotEmpty())
        new Chart(document.getElementById('chartTop'), {
            type: 'bar',
            data: {
                labels: @json($topProductos->pluck('product_name')),
                datasets: [{ data: @json($topProductos->pluck('total')), backgroundColor: '#10b981', borderRadius: 6 }]
            },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => money(c.parsed.x) } } },
                scales: { x: { ticks: { callback: (v) => cur + v }, grid: { color: '#f1f5f9' } }, y: { grid: { display: false } } } }
        });
        @endif

        @if ($porCategoria->isNotEmpty())
        new Chart(document.getElementById('chartCat'), {
            type: 'doughnut',
            data: { labels: @json($porCategoria->pluck('name')),
                datasets: [{ data: @json($porCategoria->pluck('total')), backgroundColor: ['#10b981','#a855f7','#f59e0b','#ef4444','#3b82f6','#14b8a6','#ec4899','#8b5cf6'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '60%',
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }, tooltip: { callbacks: { label: (c) => c.label + ': ' + money(c.parsed) } } } }
        });
        @endif

        @if ($porPago->isNotEmpty())
        new Chart(document.getElementById('chartPago'), {
            type: 'doughnut',
            data: { labels: @json($porPago->pluck('payment_method')),
                datasets: [{ data: @json($porPago->pluck('total')), backgroundColor: ['#10b981','#3b82f6','#a855f7','#f59e0b'], borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => money(c.parsed) } } } }
        });
        @endif
    </script>
@endsection
