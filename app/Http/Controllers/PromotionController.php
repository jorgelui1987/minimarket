<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::with('product')->latest()->paginate(12);

        return view('promotions.index', compact('promotions'));
    }

    public function create(): View
    {
        return view('promotions.form', [
            'promotion' => new Promotion,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Promotion::create($this->validated($request));

        return redirect()->route('promociones.index')->with('success', 'Promoción creada correctamente.');
    }

    public function edit(Promotion $promocion): View
    {
        return view('promotions.form', [
            'promotion' => $promocion,
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Promotion $promocion): RedirectResponse
    {
        $promocion->update($this->validated($request));

        return redirect()->route('promociones.index')->with('success', 'Promoción actualizada correctamente.');
    }

    public function destroy(Promotion $promocion): RedirectResponse
    {
        $promocion->delete();

        return redirect()->route('promociones.index')->with('success', 'Promoción eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_id' => ['nullable', 'exists:products,id'],
            'type' => ['required', 'in:descuento,2x1'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $data['discount_percent'] = $data['discount_percent'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
