<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeSubcategoryController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Subcategories/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Subcategories/partials/CreateUpdate', [
            'mode' => 'create',
            'subcategory' => null,
        ]);
    }

    public function edit(int $subcategory)
    {
        return Inertia::render('Shoes/Subcategories/partials/CreateUpdate', [
            'mode' => 'edit',
            'subcategory' => ShoeCatalogData::subcategory($subcategory),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.subcategories.index')
            ->with('success', 'Shoe subcategory saved.');
    }

    public function update(Request $request, int $subcategory)
    {
        $request->validate([
            'category_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.subcategories.index')
            ->with('success', "Shoe subcategory {$subcategory} updated.");
    }

    public function options(Request $request)
    {
        $categoryId = $request->filled('category_id')
            ? (int) $request->integer('category_id')
            : null;

        return response()->json(ShoeCatalogData::activeSubcategoryOptions($categoryId));
    }
}