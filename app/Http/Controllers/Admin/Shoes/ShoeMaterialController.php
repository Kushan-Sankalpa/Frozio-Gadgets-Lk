<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeMaterialController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Materials/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Materials/partials/CreateUpdate', [
            'mode' => 'create',
            'material' => null,
        ]);
    }

    public function edit(int $material)
    {
        return Inertia::render('Shoes/Materials/partials/CreateUpdate', [
            'mode' => 'edit',
            'material' => ShoeCatalogData::material($material),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.materials.index')
            ->with('success', 'Shoe material saved.');
    }

    public function update(Request $request, int $material)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.materials.index')
            ->with('success', "Shoe material {$material} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeMaterialOptions());
    }
}