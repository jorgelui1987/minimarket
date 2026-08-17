<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $sales = Sale::with(['customer', 'user'])
            ->withCount('items')
            ->when($request->from, fn ($q) => $q->whereDate('created_at', '>=', $request->from))
            ->when($request->to, fn ($q) => $q->whereDate('created_at', '<=', $request->to))
            ->when($request->payment, fn ($q) => $q->where('payment_method', $request->payment))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $todayTotal = (float) Sale::whereDate('created_at', Carbon::today())->sum('total');
        $todayCount = Sale::whereDate('created_at', Carbon::today())->count();

        return view('sales.index', compact('sales', 'todayTotal', 'todayCount'));
    }

    public function show(Sale $venta): View
    {
        $venta->load(['items.product', 'customer', 'user', 'electronicDocument']);

        return view('sales.show', compact('venta'));
    }
}
