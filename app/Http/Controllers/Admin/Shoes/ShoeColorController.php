<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeColorController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Colors/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Colors/partials/CreateUpdate', [
            'mode' => 'create',
            'color' => null,
        ]);
    }

    public function edit(int $color)
    {
        return Inertia::render('Shoes/Colors/partials/CreateUpdate', [
            'mode' => 'edit',
            'color' => ShoeCatalogData::color($color),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hex_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.colors.index')
            ->with('success', 'Shoe color saved.');
    }

    public function update(Request $request, int $color)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hex_code' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.colors.index')
            ->with('success', "Shoe color {$color} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeColorOptions());
    }
}