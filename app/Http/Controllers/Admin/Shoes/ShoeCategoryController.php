<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeCategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Categories/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Categories/partials/CreateUpdate', [
            'mode' => 'create',
            'category' => null,
        ]);
    }

    public function edit(int $category)
    {
        return Inertia::render('Shoes/Categories/partials/CreateUpdate', [
            'mode' => 'edit',
            'category' => ShoeCatalogData::category($category),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.categories.index')
            ->with('success', 'Shoe category saved.');
    }

    public function update(Request $request, int $category)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.categories.index')
            ->with('success', "Shoe category {$category} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeCategoryOptions());
    }
}