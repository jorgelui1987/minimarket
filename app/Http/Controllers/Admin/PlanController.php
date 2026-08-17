<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('tenants')->orderBy('price')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function create(): View
    {
        return view('admin.plans.form', ['plan' => new Plan]);
    }

    public function store(Request $request): RedirectResponse
    {
        Plan::create($this->validated($request));

        return redirect()->route('admin.plans.index')->with('success', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan): View
    {
        return view('admin.plans.form', compact('plan'));
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $plan->update($this->validated($request, $plan));

        return redirect()->route('admin.plans.index')->with('success', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->tenants()->exists()) {
            return back()->with('error', 'No se puede eliminar un plan con empresas asignadas.');
        }

        $plan->delete();

        return redirect()->route('admin.plans.index')->with('success', 'Plan eliminado.');
    }

    private function validated(Request $request, ?Plan $plan = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'max_products' => ['required', 'integer', 'min:-1'],
            'max_users' => ['required', 'integer', 'min:-1'],
            'max_monthly_sales' => ['required', 'integer', 'min:-1'],
        ]);

        $data['slug'] = $plan?->slug ?? Str::slug($data['name']) . '-' . Str::random(4);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
