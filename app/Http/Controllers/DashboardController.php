<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Valores por defecto seguros: el dashboard NUNCA debe dar error 500,
        // aunque falte una tabla o no haya datos todavía.
        $ventasHoy = 0.0;
        $ventasMes = 0.0;
        $ticketsHoy = 0;
        $ticketsMes = 0;
        $totalProductos = 0;
        $stockBajo = 0;
        $totalClientes = 0;
        $porVencer = 0;
        $linea = collect();
        $porCategoria = collect();
        $formaPago = collect();
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $porDia = collect(range(0, 6))->map(fn () => 0.0);

        try {
            $today = Carbon::today();
            $startMonth = Carbon::now()->startOfMonth();
            $tid = auth()->user()->tenant_id;

            // KPI cards
            $ventasHoy = (float) Sale::whereDate('created_at', $today)->sum('total');
            $ticketsHoy = Sale::whereDate('created_at', $today)->count();

            $ventasMes = (float) Sale::where('created_at', '>=', $startMonth)->sum('total');
            $ticketsMes = Sale::where('created_at', '>=', $startMonth)->count();

            $totalProductos = Product::count();
            $stockBajo = Product::whereColumn('stock', '<=', 'min_stock')->count();

            $totalClientes = Customer::count();
            $porVencer = Customer::whereNotNull('birthday')->count();

            // Línea: ventas últimos 7 días
            $linea = collect(range(6, 0))->map(function ($d) {
                $start = Carbon::today()->subDays($d)->startOfDay();
                $end = Carbon::today()->subDays($d)->endOfDay();
                return [
                    'label' => $start->locale('es')->isoFormat('DD MMM'),
                    'total' => (float) Sale::whereBetween('created_at', [$start, $end])->sum('total'),
                ];
            });

            // Donut: ventas por categoría (últimos 30 días)
            $porCategoria = DB::table('sale_items')
                ->join('products', 'products.id', '=', 'sale_items.product_id')
                ->join('categories', 'categories.id', '=', 'products.category_id')
                ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
                ->where('sales.created_at', '>=', Carbon::now()->subDays(30))
                ->where('sales.tenant_id', $tid)
                ->select('categories.name', DB::raw('SUM(sale_items.subtotal) as total'))
                ->groupBy('categories.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            // Barras: ventas por día de la semana (últimos 30 días)
            $porDiaRaw = Sale::where('created_at', '>=', Carbon::now()->subDays(30))
                ->get()
                ->groupBy(fn ($s) => Carbon::parse($s->created_at)->dayOfWeek)
                ->map(fn ($g) => (float) $g->sum('total'));
            $porDia = collect(range(0, 6))->map(fn ($i) => round($porDiaRaw->get($i, 0), 2));

            // Donut: forma de pago (últimos 30 días)
            $formaPago = Sale::where('created_at', '>=', Carbon::now()->subDays(30))
                ->select('payment_method', DB::raw('SUM(total) as total'))
                ->groupBy('payment_method')
                ->orderByDesc('total')
                ->get();
        } catch (\Throwable $e) {
            // Si algo falla (tabla faltante, etc.) se registra y se muestran ceros
            // en lugar de romper la pantalla principal.
            report($e);
        }

        return view('dashboard', compact(
            'ventasHoy', 'ticketsHoy', 'ventasMes', 'ticketsMes',
            'totalProductos', 'stockBajo', 'totalClientes', 'porVencer',
            'linea', 'porCategoria', 'dias', 'porDia', 'formaPago'
        ));
    }
}
