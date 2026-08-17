<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ], [], [
            'business_name' => 'nombre del negocio',
            'name' => 'nombre',
            'email' => 'correo',
            'password' => 'contraseña',
        ]);

        $user = DB::transaction(function () use ($request) {
            $plan = Plan::where('slug', 'prueba')->first() ?? Plan::first();

            $tenant = Tenant::create([
                'name' => $request->business_name,
                'plan_id' => $plan?->id,
                'status' => 'trial',
                'contact_email' => $request->email,
                'trial_ends_at' => Carbon::now()->addDays(14),
                'is_active' => true,
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'is_super_admin' => false,
                'is_active' => true,
            ]);

            // Categorías iniciales para arrancar
            Auth::login($user);
            foreach (['Abarrotes', 'Bebidas', 'Lácteos', 'Limpieza', 'Snacks'] as $cat) {
                Category::create(['name' => $cat, 'is_active' => true]);
            }

            return $user;
        });

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', '¡Bienvenido! Tu empresa fue creada con 14 días de prueba.');
    }
}
