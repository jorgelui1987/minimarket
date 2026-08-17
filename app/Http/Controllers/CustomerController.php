<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = Customer::when($request->q, fn ($query) => $query
                ->where('name', 'like', "%{$request->q}%")
                ->orWhere('doc_number', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.form', ['customer' => new Customer]);
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::create($this->validated($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Customer $cliente): View
    {
        return view('customers.form', ['customer' => $cliente]);
    }

    public function update(Request $request, Customer $cliente): RedirectResponse
    {
        $cliente->update($this->validated($request));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $cliente): RedirectResponse
    {
        $cliente->delete();

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'doc_type' => ['required', 'string', 'max:10'],
            'doc_number' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'birthday' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
