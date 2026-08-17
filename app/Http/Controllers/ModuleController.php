<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * Renderiza una vista de módulo "en construcción" reutilizable.
     * Cada módulo del menú apunta aquí hasta que se implemente su CRUD.
     */
    public function show(string $title, string $description = ''): View
    {
        return view('modules.placeholder', compact('title', 'description'));
    }
}
