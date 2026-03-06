<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeBrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Brands/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Brands/partials/CreateUpdate', [
            'mode' => 'create',
            'brand' => null,
        ]);
    }

    public function edit(int $brand)
    {
        return Inertia::render('Shoes/Brands/partials/CreateUpdate', [
            'mode' => 'edit',
            'brand' => ShoeCatalogData::brand($brand),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.brands.index')
            ->with('success', 'Shoe brand saved.');
    }

    public function update(Request $request, int $brand)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.brands.index')
            ->with('success', "Shoe brand {$brand} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeBrandOptions());
    }
}