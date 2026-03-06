<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Types/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Types/partials/CreateUpdate', [
            'mode' => 'create',
            'type' => null,
        ]);
    }

    public function edit(int $type)
    {
        return Inertia::render('Shoes/Types/partials/CreateUpdate', [
            'mode' => 'edit',
            'type' => ShoeCatalogData::type($type),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.types.index')
            ->with('success', 'Shoe type saved.');
    }

    public function update(Request $request, int $type)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.types.index')
            ->with('success', "Shoe type {$type} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeTypeOptions());
    }
}