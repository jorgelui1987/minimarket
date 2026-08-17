<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ventas</title>
    <style>
        * { font-family: 'Segoe UI', Arial, sans-serif; box-sizing: border-box; }
        body { margin: 32px; color: #1e293b; font-size: 13px; }
        h1 { font-size: 20px; margin: 0; }
        h2 { font-size: 14px; margin: 24px 0 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; }
        .muted { color: #64748b; font-size: 12px; }
        .kpis { display: flex; gap: 12px; margin-top: 16px; }
        .kpi { flex: 1; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; }
        .kpi p { margin: 0; }
        .kpi .label { color: #64748b; font-size: 11px; }
        .kpi .value { font-size: 18px; font-weight: 800; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { padding: 6px 8px; text-align: left; border-bottom: 1px solid #eef2f7; }
        th { background: #f8fafc; color: #475569; font-size: 11px; text-transform: uppercase; }
        td.r, th.r { text-align: right; }
        td.c, th.c { text-align: center; }
        .foot { margin-top: 28px; color: #94a3b8; font-size: 11px; }
        @media print { body { margin: 12px; } .noprint { display: none; } }
    </style>
</head>
<body onload="window.print()">
    <div class="noprint" style="margin-bottom:16px;">
        <button onclick="window.print()" style="padding:8px 16px;border:0;border-radius:8px;background:#4f46e5;color:#fff;font-weight:600;cursor:pointer;">Imprimir</button>
    </div>

    <h1>{{ \App\Models\Setting::get('business_name', 'Mi Minimarket') }}</h1>
    <p class="muted">Reporte de ventas · {{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }} · generado el {{ now()->format('d/m/Y H:i') }}</p>

    <div class="kpis">
        <div class="kpi"><p class="label">Ventas totales</p><p class="value">S/ {{ number_format($totalVentas, 2) }}</p></div>
        <div class="kpi"><p class="label">N° de ventas</p><p class="value">{{ $numVentas }}</p></div>
        <div class="kpi"><p class="label">Ticket promedio</p><p class="value">S/ {{ number_format($ticketPromedio, 2) }}</p></div>
        <div class="kpi"><p class="label">Utilidad estimada</p><p class="value">S/ {{ number_format($utilidad, 2) }}</p></div>
    </div>

    <h2>Productos más vendidos</h2>
    <table>
        <thead><tr><th>Producto</th><th class="c">Cantidad</th><th class="r">Importe</th></tr></thead>
        <tbody>
            @forelse ($topProductos as $p)
                <tr><td>{{ $p->product_name }}</td><td class="c">{{ $p->qty }}</td><td class="r">S/ {{ number_format($p->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="3" class="muted">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas por categoría</h2>
    <table>
        <thead><tr><th>Categoría</th><th class="r">Importe</th></tr></thead>
        <tbody>
            @forelse ($porCategoria as $c)
                <tr><td>{{ $c->name }}</td><td class="r">S/ {{ number_format($c->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas por cajero</h2>
    <table>
        <thead><tr><th>Usuario</th><th class="c">N° ventas</th><th class="r">Importe</th></tr></thead>
        <tbody>
            @forelse ($porUsuario as $u)
                <tr><td>{{ $u->name ?? '—' }}</td><td class="c">{{ $u->num }}</td><td class="r">S/ {{ number_format($u->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="3" class="muted">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Ventas por forma de pago</h2>
    <table>
        <thead><tr><th>Método</th><th class="r">Importe</th></tr></thead>
        <tbody>
            @forelse ($porPago as $p)
                <tr><td>{{ ucfirst($p->payment_method) }}</td><td class="r">S/ {{ number_format($p->total, 2) }}</td></tr>
            @empty
                <tr><td colspan="2" class="muted">Sin datos.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="foot">Documento generado automáticamente por el sistema POS.</p>
</body>
</html>
