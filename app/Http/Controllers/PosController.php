<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Services\Billing\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(private readonly BillingService $billing)
    {
    }

    public function index(): View
    {
        $products = Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'stock', 'unit', 'barcode', 'category_id']);

        $customers = Customer::orderBy('name')->get(['id', 'name', 'doc_number']);
        $current = CashRegister::current();

        // Impuesto dinámico: lee de configuración (0% si así está) y nombre según país
        $pais = \App\Services\Billing\BillingSettings::country();
        $igvPercent = (float) \App\Models\Setting::get('igv_percent', $pais === 'CL' ? '19' : '18');
        $taxLabel = $pais === 'CL' ? 'IVA' : 'IGV';

        return view('pos.index', compact('products', 'customers', 'current', 'igvPercent', 'taxLabel', 'pais'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'payment_method' => ['required', 'in:efectivo,tarjeta,yape,plin'],
            'document_type' => ['required', 'in:boleta,factura,ticket'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
        ], [
            'items.required' => 'El carrito está vacío.',
        ]);

        try {
            $sale = DB::transaction(function () use ($data, $request) {
                $current = CashRegister::current();

                $sale = Sale::create([
                    'document_type' => $data['document_type'],
                    'series' => $data['document_type'] === 'factura' ? 'F001' : 'B001',
                    'customer_id' => $data['customer_id'] ?? null,
                    'user_id' => $request->user()->id,
                    'cash_register_id' => $current?->id,
                    'payment_method' => $data['payment_method'],
                    'status' => 'pagado',
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                ]);
                $sale->update(['number' => str_pad((string) $sale->id, 6, '0', STR_PAD_LEFT)]);

                $total = 0;
                foreach ($data['items'] as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);
                    $qty = (float) $item['quantity'];

                    if ($product->stock < $qty) {
                        throw new \RuntimeException("Stock insuficiente para {$product->name} (disponible: {$product->stock}).");
                    }

                    $lineTotal = round($product->price * $qty, 2);
                    $total += $lineTotal;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'price' => $product->price,
                        'quantity' => $qty,
                        'subtotal' => $lineTotal,
                    ]);

                    $product->decrement('stock', $qty);
                    StockMovement::record($product, -$qty, 'salida', 'venta', $sale->id, 'Venta ' . $sale->series . '-' . $sale->number);
                }

                // Impuesto dinámico según configuración (0% si así está configurado)
                $pais = \App\Services\Billing\BillingSettings::country();
                $igvPercent = (float) \App\Models\Setting::get('igv_percent', $pais === 'CL' ? '19' : '18');
                $taxRate = $igvPercent / 100;
                $tax = $taxRate > 0 ? round($total - ($total / (1 + $taxRate)), 2) : 0;
                $sale->update(['subtotal' => round($total - $tax, 2), 'tax' => $tax, 'total' => $total]);

                return $sale;
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        // Facturación electrónica: emitir tras confirmar la venta. Va FUERA de la
        // transacción y protegido: si el servicio falla, la venta ya está guardada
        // y el comprobante queda para reintentar desde su pantalla.
        if (\App\Services\Billing\BillingSettings::autoEmit() && $this->billing->facturable($sale)) {
            try {
                $this->billing->emitForSale($sale);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('ventas.show', $sale)->with('success', 'Venta registrada correctamente. Comprobante ' . $sale->series . '-' . $sale->number);
    }
}
