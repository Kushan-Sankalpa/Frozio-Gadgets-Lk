<?php

namespace App\Http\Controllers;

use App\Models\ColorOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ColorOptionController extends Controller
{
    public function index()
    {
        return Inertia::render('Colors/index');
    }

    public function create()
    {
        return Inertia::render('Colors/partials/CreateUpdate', [
            'mode' => 'create',
            'color' => null,
        ]);
    }

    public function edit(ColorOption $colorOption)
    {
        return Inertia::render('Colors/partials/CreateUpdate', [
            'mode' => 'edit',
            'color' => [
                'id' => $colorOption->id,
                'name' => $colorOption->name,
                'status' => $colorOption->status,
                'image_url' => $colorOption->image_url,
            ],
        ]);
    }

    public function image(ColorOption $colorOption)
    {
        if (!$colorOption->image_path) {
            abort(404);
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($colorOption->image_path)) {
            abort(404);
        }

        return response()->file($disk->path($colorOption->image_path), [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:color_options,name'],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $path = null;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('colors', 'public');
        }

        ColorOption::create([
            'name' => $validated['name'],
            'status' => $validated['status'],
            'image_path' => $path,
        ]);

        return redirect()->route('colors.index')->with('success', 'Color created.');
    }

    public function update(Request $request, ColorOption $colorOption)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255', 'unique:color_options,name,' . $colorOption->id],
            'status' => ['required', 'in:active,inactive'],
            'image'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($colorOption->image_path && Storage::disk('public')->exists($colorOption->image_path)) {
                Storage::disk('public')->delete($colorOption->image_path);
            }

            $colorOption->image_path = $request->file('image')->store('colors', 'public');
        }

        $colorOption->name = $validated['name'];
        $colorOption->status = $validated['status'];
        $colorOption->save();

        return redirect()->route('colors.index')->with('success', 'Color updated.');
    }

    public function destroy(ColorOption $colorOption)
    {
        if ($colorOption->image_path && Storage::disk('public')->exists($colorOption->image_path)) {
            Storage::disk('public')->delete($colorOption->image_path);
        }

        $colorOption->delete();

        return redirect()->route('colors.index')->with('success', 'Color deleted.');
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = $request->input('search.value');

        $q = ColorOption::query();
        $recordsTotal = (clone $q)->count();

        if ($searchValue) {
            $q->where(function ($x) use ($searchValue) {
                $x->where('name', 'like', "%{$searchValue}%")
                  ->orWhere('status', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $q)->count();

        $orderColIndex = (int) $request->input('order.0.column', 0);
        $orderDir      = $request->input('order.0.dir', 'desc');

        $columns = [
            0 => 'id',
            1 => 'image_path',
            2 => 'name',
            3 => 'status',
        ];

        $q->orderBy($columns[$orderColIndex] ?? 'id', $orderDir);

        $rows = $q->skip($start)->take($length)->get();

        $data = $rows->map(function (ColorOption $c) {
            $statusBadge = $c->status === 'active'
                ? '<span class="inline-flex items-center rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Active</span>'
                : '<span class="inline-flex items-center rounded-full bg-neutral-200 px-3 py-1 text-xs font-medium text-neutral-700">Inactive</span>';

            $imageHtml = $c->image_url
                ? '<div class="flex items-center justify-center">
                        <img src="' . e($c->image_url) . '" alt="' . e($c->name) . '" class="h-12 w-12 rounded-lg object-cover border border-neutral-200 bg-white" />
                   </div>'
                : '<div class="flex items-center justify-center">
                        <div class="h-12 w-12 rounded-lg border border-dashed border-neutral-300 bg-neutral-100 text-[10px] text-neutral-500 flex items-center justify-center">
                            No Image
                        </div>
                   </div>';

            $actions = '
              <div class="flex items-center gap-2">
                <button type="button" data-action="edit" data-id="'.$c->id.'" data-name="'.e($c->name).'"
                  class="rounded-full border border-neutral-200 px-3 py-1.5 text-xs font-medium text-neutral-700 hover:bg-neutral-100">Edit</button>
                <button type="button" data-action="delete" data-id="'.$c->id.'" data-name="'.e($c->name).'"
                  class="rounded-full border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Delete</button>
              </div>';

            return [
                'id' => $c->id,
                'image_html' => $imageHtml,
                'name' => e($c->name),
                'status_badge' => $statusBadge,
                'actions' => $actions,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
}