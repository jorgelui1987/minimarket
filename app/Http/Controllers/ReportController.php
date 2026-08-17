<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->gather($request));
    }

    /** Exporta el reporte del periodo a CSV (compatible con Excel). */
    public function export(Request $request): StreamedResponse
    {
        $d = $this->gather($request);
        $filename = 'reporte_' . $d['from']->format('Ymd') . '_' . $d['to']->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($d) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 para acentos en Excel

            $put = fn ($row) => fputcsv($out, $row, ';');

            $put(['REPORTE DE VENTAS']);
            $put(['Periodo', $d['from']->format('d/m/Y'), 'al', $d['to']->format('d/m/Y')]);
            $put([]);
            $put(['Total ventas', number_format($d['totalVentas'], 2)]);
            $put(['N° de ventas', $d['numVentas']]);
            $put(['Ticket promedio', number_format($d['ticketPromedio'], 2)]);
            $put(['Utilidad estimada', number_format($d['utilidad'], 2)]);
            $put([]);

            $put(['PRODUCTOS MÁS VENDIDOS']);
            $put(['Producto', 'Cantidad', 'Importe']);
            foreach ($d['topProductos'] as $p) {
                $put([$p->product_name, $p->qty, number_format($p->total, 2)]);
            }
            $put([]);

            $put(['VENTAS POR CATEGORÍA']);
            $put(['Categoría', 'Importe']);
            foreach ($d['porCategoria'] as $c) {
                $put([$c->name, number_format($c->total, 2)]);
            }
            $put([]);

            $put(['VENTAS POR CAJERO']);
            $put(['Usuario', 'N° ventas', 'Importe']);
            foreach ($d['porUsuario'] as $u) {
                $put([$u->name ?? '—', $u->num, number_format($u->total, 2)]);
            }
            $put([]);

            $put(['VENTAS POR FORMA DE PAGO']);
            $put(['Método', 'Importe']);
            foreach ($d['porPago'] as $p) {
                $put([ucfirst($p->payment_method), number_format($p->total, 2)]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Versión imprimible (PDF vía navegador). */
    public function print(Request $request): View
    {
        return view('reports.print', $this->gather($request));
    }

    /** Reúne todos los datos del reporte para el periodo dado. */
    private function gather(Request $request): array
    {
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->to ? Carbon::parse($request->to)->endOfDay() : Carbon::now()->endOfDay();
        $tid = auth()->user()->tenant_id;

        $sales = Sale::whereBetween('created_at', [$from, $to]);
        $totalVentas = (float) (clone $sales)->sum('total');
        $numVentas = (clone $sales)->count();
        $ticketPromedio = $numVentas > 0 ? $totalVentas / $numVentas : 0;

        $utilidad = (float) DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->where('sales.tenant_id', $tid)
            ->select(DB::raw('SUM((sale_items.price - COALESCE(products.cost, 0)) * sale_items.quantity) as profit'))
            ->value('profit') ?? 0;

        $topProductos = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->where('sales.tenant_id', $tid)
            ->select('sale_items.product_name', DB::raw('SUM(sale_items.quantity) as qty'), DB::raw('SUM(sale_items.subtotal) as total'))
            ->groupBy('sale_items.product_name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $porCategoria = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereBetween('sales.created_at', [$from, $to])
            ->where('sales.tenant_id', $tid)
            ->select('categories.name', DB::raw('SUM(sale_items.subtotal) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $porUsuario = Sale::whereBetween('sales.created_at', [$from, $to])
            ->leftJoin('users', 'users.id', '=', 'sales.user_id')
            ->select('users.name', DB::raw('COUNT(*) as num'), DB::raw('SUM(sales.total) as total'))
            ->groupBy('users.name')
            ->orderByDesc('total')
            ->get();

        $porPago = (clone $sales)
            ->select('payment_method', DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();

        $stockBajo = Product::whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->limit(15)
            ->get();

        return compact(
            'from', 'to', 'totalVentas', 'numVentas', 'ticketPromedio', 'utilidad',
            'topProductos', 'porCategoria', 'porUsuario', 'porPago', 'stockBajo'
        );
    }
}
