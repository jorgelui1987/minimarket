<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')
            ->when($request->q, fn ($query) => $query->where('name', 'like', "%{$request->q}%"))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    public function edit(Category $categoria): View
    {
        return view('categories.form', ['category' => $categoria]);
    }

    public function update(Request $request, Category $categoria): RedirectResponse
    {
        $categoria->update($this->validated($request));

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Category $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }
}
