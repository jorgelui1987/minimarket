<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $suppliers = Supplier::when($request->q, fn ($query) => $query
                ->where('name', 'like', "%{$request->q}%")
                ->orWhere('ruc', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.form', ['supplier' => new Supplier]);
    }

    public function store(Request $request): RedirectResponse
    {
        Supplier::create($this->validated($request));

        return redirect()->route('proveedores.index')->with('success', 'Proveedor registrado correctamente.');
    }

    public function edit(Supplier $proveedore): View
    {
        return view('suppliers.form', ['supplier' => $proveedore]);
    }

    public function update(Request $request, Supplier $proveedore): RedirectResponse
    {
        $proveedore->update($this->validated($request));

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $proveedore): RedirectResponse
    {
        $proveedore->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:11'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
