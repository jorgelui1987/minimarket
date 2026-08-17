<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::when($request->q, fn ($query) => $query
                ->where('name', 'like', "%{$request->q}%")
                ->orWhere('email', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,cajero,almacen'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $tenant = $request->user()->tenant;
        if ($tenant && $tenant->reachedUserLimit(User::count())) {
            return back()->withInput()->with('error', 'Alcanzaste el límite de usuarios de tu plan (' . $tenant->plan->max_users . '). Mejora tu plan para agregar más.');
        }

        $data['is_active'] = $request->boolean('is_active');
        User::create($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario): View
    {
        return view('users.form', ['user' => $usuario]);
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'role' => ['required', 'in:admin,cajero,almacen'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'confirmed', Password::min(6)],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $usuario->update($data);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $usuario): RedirectResponse
    {
        if ($usuario->id === $request->user()->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado.');
    }
}
