<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        $tenant = auth()->user()->tenant->load('plan');
        $plans = Plan::where('is_active', true)->orderBy('price')->get();

        $usage = [
            'products' => Product::count(),
            'users' => User::count(),
            'sales_month' => Sale::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];

        return view('subscription.index', compact('tenant', 'plans', 'usage'));
    }
}
