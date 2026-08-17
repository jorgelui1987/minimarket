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

        return view('inventory.index', compact('sinStock', 'stockBajo', 'totalProductos', 'valorInventario'));
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
}
