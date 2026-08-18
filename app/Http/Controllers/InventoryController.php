<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /** Panel de alertas de stock. */
    public function index(): View
    {
        $sinStock = Product::where('is_active', true)->where('stock', '<=', 0)
            ->orderBy('name')->get();

        $stockBajo = Product::where('is_active', true)
            ->whereColumn('stock', '<=', 'min_stock')
            ->where('stock', '>', 0)
            ->orderBy('stock')->get();

        $totalProductos = Product::count();
        $valorInventario = (float) Product::sum(DB::raw('cost * stock'));
        $productos = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'unit', 'stock']);

        return view('inventory.index', compact('sinStock', 'stockBajo', 'totalProductos', 'valorInventario', 'productos'));
    }

    /** Kardex: historial de movimientos de inventario. */
    public function kardex(Request $request): View
    {
        $productId = $request->integer('product_id') ?: null;

        $movements = StockMovement::with(['product', 'user'])
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('inventory.kardex', compact('movements', 'products', 'productId'));
    }

    /** Ajuste manual de stock. */
    public function adjust(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'new_stock' => ['required', 'integer', 'min:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $change = (int) $data['new_stock'] - (int) $product->stock;

        if ($change === 0) {
            return back()->with('error', 'El nuevo stock es igual al actual; no se registró ningún ajuste.');
        }

        DB::transaction(function () use ($product, $data, $change) {
            $product->update(['stock' => (int) $data['new_stock']]);
            StockMovement::record(
                $product,
                $change,
                'ajuste',
                'ajuste',
                null,
                $data['note'] ?: 'Ajuste manual de inventario'
            );
        });

        return back()->with('success', "Stock de «{$product->name}» ajustado correctamente.");
    }

    /** Reporte de mermas (semanal / por producto / por motivo). */
    public function mermaReport(Request $request): View
    {
        $desde = $request->date('desde')?->startOfDay();
        $hasta = $request->date('hasta')?->endOfDay();

        $query = StockMovement::with(['product', 'user'])
            ->where('type', 'merma')
            ->when($desde, fn ($q) => $q->where('created_at', '>=', $desde))
            ->when($hasta, fn ($q) => $q->where('created_at', '<=', $hasta));

        // Totales por producto
        $porProducto = (clone $query)
            ->select('product_id', DB::raw('SUM(ABS(quantity)) as total_merma'), DB::raw('COUNT(*) as veces'))
            ->groupBy('product_id')
            ->orderByDesc('total_merma')
            ->get();

        // Totales por motivo
        $porMotivo = (clone $query)
            ->select('note', DB::raw('SUM(ABS(quantity)) as total_merma'), DB::raw('COUNT(*) as veces'))
            ->groupBy('note')
            ->orderByDesc('total_merma')
            ->get();

        $totalMerma = (float) (clone $query)->sum(DB::raw('ABS(quantity)'));
        $movimientos = $query->latest()->paginate(25)->withQueryString();

        return view('inventory.merma', compact('porProducto', 'porMotivo', 'totalMerma', 'movimientos', 'desde', 'hasta'));
    }

    /** Registra merma (pérdida de producto por descomposición, daño, etc.). */
    public function merma(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $quantity = (float) $data['quantity'];

        if ($quantity > (float) $product->stock) {
            return back()->with('error', "La merma ({$quantity}) no puede ser mayor al stock actual ({$product->stock}).");
        }

        DB::transaction(function () use ($product, $quantity, $data) {
            $nuevoStock = (float) $product->stock - $quantity;
            $product->update(['stock' => $nuevoStock]);
            StockMovement::record(
                $product,
                -$quantity,
                'merma',
                'merma',
                null,
                $data['motivo']
            );
        });

        return back()->with('success', "Merma de «{$product->name}» registrada: {$quantity} unidades.");
    }
}
