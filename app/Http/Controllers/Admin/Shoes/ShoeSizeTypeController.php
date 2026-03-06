<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeSizeTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/SizeTypes/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/SizeTypes/partials/CreateUpdate', [
            'mode' => 'create',
            'sizeType' => null,
        ]);
    }

    public function edit(int $sizeType)
    {
        return Inertia::render('Shoes/SizeTypes/partials/CreateUpdate', [
            'mode' => 'edit',
            'sizeType' => ShoeCatalogData::sizeType($sizeType),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.size-types.index')
            ->with('success', 'Shoe size type saved.');
    }

    public function update(Request $request, int $sizeType)
    {
        $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        return redirect()
            ->route('admin.shoes.size-types.index')
            ->with('success', "Shoe size type {$sizeType} updated.");
    }

    public function options()
    {
        return response()->json(ShoeCatalogData::activeSizeTypeOptions());
    }
}