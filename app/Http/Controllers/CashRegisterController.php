<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashRegisterController extends Controller
{
    public function index(): View
    {
        $current = CashRegister::current();
        $current?->load(['movements.user', 'user']);

        // Ventas en efectivo desde que se abrió la caja
        $cashSales = 0;
        $salesCount = 0;
        if ($current) {
            $cashSales = (float) Sale::where('cash_register_id', $current->id)
                ->where('payment_method', 'efectivo')->sum('total');
            $salesCount = Sale::where('cash_register_id', $current->id)->count();
        }

        $history = CashRegister::with('user')
            ->where('status', 'cerrada')
            ->latest('closed_at')
            ->paginate(8);

        return view('cash.index', compact('current', 'cashSales', 'salesCount', 'history'));
    }

    public function open(Request $request): RedirectResponse
    {
        if (CashRegister::current()) {
            return back()->with('error', 'Ya existe una caja abierta.');
        }

        $data = $request->validate([
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ]);

        CashRegister::create([
            'user_id' => $request->user()->id,
            'opening_amount' => $data['opening_amount'],
            'status' => 'abierta',
            'opened_at' => Carbon::now(),
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja abierta correctamente.');
    }

    public function movement(Request $request): RedirectResponse
    {
        $current = CashRegister::current();
        if (! $current) {
            return back()->with('error', 'No hay una caja abierta.');
        }

        $data = $request->validate([
            'type' => ['required', 'in:ingreso,egreso'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'concept' => ['required', 'string', 'max:255'],
        ]);

        CashMovement::create($data + [
            'cash_register_id' => $current->id,
            'user_id' => $request->user()->id,
        ]);

        return redirect()->route('caja.index')->with('success', 'Movimiento registrado.');
    }

    public function close(Request $request): RedirectResponse
    {
        $current = CashRegister::current();
        if (! $current) {
            return back()->with('error', 'No hay una caja abierta.');
        }

        $data = $request->validate([
            'closing_amount' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $expected = $this->expectedAmount($current);

        $current->update([
            'closing_amount' => $data['closing_amount'],
            'expected_amount' => $expected,
            'difference' => round($data['closing_amount'] - $expected, 2),
            'notes' => $data['notes'] ?? null,
            'status' => 'cerrada',
            'closed_at' => Carbon::now(),
        ]);

        return redirect()->route('caja.index')->with('success', 'Caja cerrada. Monto esperado: S/ ' . number_format($expected, 2));
    }

    private function expectedAmount(CashRegister $register): float
    {
        $cashSales = (float) Sale::where('cash_register_id', $register->id)
            ->where('payment_method', 'efectivo')->sum('total');
        $ingresos = (float) $register->movements()->where('type', 'ingreso')->sum('amount');
        $egresos = (float) $register->movements()->where('type', 'egreso')->sum('amount');

        return round($register->opening_amount + $cashSales + $ingresos - $egresos, 2);
    }
}
