<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function index(): View
    {
        $purchases = Purchase::with('supplier')
            ->withCount('items')
            ->latest()
            ->paginate(12);

        return view('purchases.index', compact('purchases'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(['id', 'name', 'cost', 'stock', 'unit']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'document' => ['nullable', 'string', 'max:50'],
            'photo_receipt' => ['nullable', 'image', 'max:5120'],  // hasta 5MB (foto boleta)
            'photo_products' => ['nullable', 'image', 'max:5120'], // hasta 5MB (foto productos)
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.cost' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
        ], [
            'items.required' => 'Debes agregar al menos un producto a la compra.',
        ]);

        DB::transaction(function () use ($data, $request) {
            // Guarda las fotos si vienen del formulario
            $photoReceipt = null;
            $photoProducts = null;
            if ($request->hasFile('photo_receipt')) {
                $photoReceipt = $request->file('photo_receipt')->store('compras/comprobantes', 'public');
            }
            if ($request->hasFile('photo_products')) {
                $photoProducts = $request->file('photo_products')->store('compras/productos', 'public');
            }

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'] ?? null,
                'user_id' => $request->user()->id,
                'document' => $data['document'] ?? null,
                'photo_receipt' => $photoReceipt,
                'photo_products' => $photoProducts,
                'status' => 'recibido',
                'total' => 0,
            ]);

            $total = 0;
            foreach ($data['items'] as $item) {
                $subtotal = round($item['cost'] * $item['quantity'], 2);
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'cost' => $item['cost'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                // Ingresa stock y actualiza el costo del producto
                $product = Product::find($item['product_id']);
                $product->increment('stock', (int) $item['quantity']);
                $product->update(['cost' => $item['cost']]);
                StockMovement::record($product, (int) $item['quantity'], 'entrada', 'compra', $purchase->id, 'Compra ' . ($purchase->document ?: '#' . $purchase->id));
            }

            $purchase->update(['total' => $total]);
        });

        return redirect()->route('compras.index')->with('success', 'Compra registrada y stock actualizado.');
    }

    public function show(Purchase $compra): View
    {
        $compra->load(['supplier', 'items.product', 'user']);

        return view('purchases.show', compact('compra'));
    }
}
