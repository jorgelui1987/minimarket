<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'supplier'])
            ->when($request->q, fn ($query) => $query->where('name', 'like', "%{$request->q}%")
                ->orWhere('barcode', 'like', "%{$request->q}%"))
            ->when($request->category, fn ($query) => $query->where('category_id', $request->category))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenant = $request->user()->tenant;
        if ($tenant && $tenant->reachedProductLimit(Product::count())) {
            return back()->withInput()->with('error', 'Alcanzaste el límite de productos de tu plan (' . $tenant->plan->max_products . '). Mejora tu plan para agregar más.');
        }

        Product::create($this->validated($request));

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $producto): View
    {
        return view('products.form', [
            'product' => $producto,
            'categories' => Category::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $producto): RedirectResponse
    {
        $producto->update($this->validated($request));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('productos.index')->with('success', 'Producto eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'unit' => ['required', 'string', 'max:10'],
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
