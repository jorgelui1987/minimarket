<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReturnController extends Controller
{
    public function index(): View
    {
        $returns = SaleReturn::with(['sale', 'user'])
            ->withCount('items')
            ->latest()
            ->paginate(15);

        return view('returns.index', compact('returns'));
    }

    public function create(Request $request): View
    {
        $sales = Sale::where('status', 'pagado')
            ->latest()
            ->limit(100)
            ->get(['id', 'series', 'number', 'total', 'created_at']);

        $sale = null;
        $lines = collect();

        if ($request->filled('sale_id')) {
            $sale = Sale::with('items')->find($request->sale_id);

            if ($sale) {
                // Cantidad ya devuelta por producto en esta venta
                $devueltos = SaleReturnItem::whereIn('sale_return_id', $sale->returns()->pluck('id'))
                    ->select('product_id', DB::raw('SUM(quantity) as qty'))
                    ->groupBy('product_id')
                    ->pluck('qty', 'product_id');

                $lines = $sale->items->map(function ($item) use ($devueltos) {
                    $yaDevuelto = (int) ($devueltos[$item->product_id] ?? 0);

                    return (object) [
                        'item' => $item,
                        'max' => max(0, (int) $item->quantity - $yaDevuelto),
                    ];
                });
            }
        }

        return view('returns.create', compact('sales', 'sale', 'lines'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sale_id' => ['required', 'exists:sales,id'],
            'reason' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ], [
            'items.required' => 'Selecciona al menos un producto a devolver.',
        ]);

        $sale = Sale::with('items')->findOrFail($data['sale_id']);

        // Filtra solo las líneas con cantidad > 0
        $aDevolver = collect($data['items'])->filter(fn ($i) => (int) $i['quantity'] > 0);

        if ($aDevolver->isEmpty()) {
            return back()->with('error', 'No indicaste ninguna cantidad a devolver.')->withInput();
        }

        try {
            $return = DB::transaction(function () use ($sale, $aDevolver, $data) {
                $current = CashRegister::current();

                $return = SaleReturn::create([
                    'sale_id' => $sale->id,
                    'user_id' => auth()->id(),
                    'cash_register_id' => $current?->id,
                    'total' => 0,
                    'reason' => $data['reason'] ?? null,
                ]);

                $total = 0;

                foreach ($aDevolver as $line) {
                    $saleItem = $sale->items->firstWhere('product_id', (int) $line['product_id']);
                    if (! $saleItem) {
                        throw new \RuntimeException('Un producto no pertenece a esta venta.');
                    }

                    $qty = (int) $line['quantity'];
                    if ($qty > (int) $saleItem->quantity) {
                        throw new \RuntimeException("No puedes devolver más de lo vendido de «{$saleItem->product_name}».");
                    }

                    $subtotal = round($saleItem->price * $qty, 2);
                    $total += $subtotal;

                    SaleReturnItem::create([
                        'sale_return_id' => $return->id,
                        'product_id' => $saleItem->product_id,
                        'product_name' => $saleItem->product_name,
                        'price' => $saleItem->price,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                    ]);

                    // Reponer stock + kardex
                    $product = Product::find($saleItem->product_id);
                    if ($product) {
                        $product->increment('stock', $qty);
                        StockMovement::record($product, $qty, 'entrada', 'devolucion', $return->id, "Devolución venta {$sale->series}-{$sale->number}");
                    }
                }

                $return->update(['total' => $total]);

                // Salida de caja por el reembolso (si hay caja abierta)
                if ($current) {
                    CashMovement::create([
                        'cash_register_id' => $current->id,
                        'user_id' => auth()->id(),
                        'type' => 'salida',
                        'amount' => $total,
                        'concept' => "Devolución venta {$sale->series}-{$sale->number}",
                    ]);
                }

                return $return;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('devoluciones.show', $return)->with('success', 'Devolución registrada. Stock y caja actualizados.');
    }

    public function show(SaleReturn $devolucion): View
    {
        $devolucion->load(['items', 'sale.customer', 'user']);

        return view('returns.show', compact('devolucion'));
    }
}
