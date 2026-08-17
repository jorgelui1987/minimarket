<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::with('plan')->withCount('users')
            ->when($request->q, fn ($query) => $query->where('name', 'like', "%{$request->q}%"))
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function edit(Tenant $tenant): View
    {
        return view('admin.tenants.edit', [
            'tenant' => $tenant->loadCount('users'),
            'plans' => Plan::orderBy('price')->get(),
        ]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'ruc' => ['nullable', 'string', 'max:11'],
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:trial,active,suspended'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'trial_ends_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $tenant->update($data);

        return redirect()->route('admin.tenants.index')->with('success', 'Empresa actualizada correctamente.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Empresa eliminada.');
    }
}
