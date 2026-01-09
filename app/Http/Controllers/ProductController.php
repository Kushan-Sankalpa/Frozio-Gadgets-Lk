<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\Product;
use App\Models\RamOption;
use App\Models\StorageOption;
use App\Models\WarrantyOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $brands = Brand::with('categories:id')->get()->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        return Inertia::render('Products/index', [
            'categories' => Category::select('id','name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id','name')->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'image_url' => $c->image_url,
            ]),
            'warranties' => WarrantyOption::select('id','name')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        $brands = Brand::with('categories:id')->get()->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        return Inertia::render('Products/CreateUpdate', [
            'mode' => 'create',
            'product' => null,
            'categories' => Category::select('id','name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id','name')->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'image_url' => $c->image_url,
            ]),
            'warranties' => WarrantyOption::select('id','name')->orderBy('name')->get(),
            'storages' => StorageOption::orderBy('value')->get()->map(fn($s) => [
                'id' => $s->id,
                'label' => trim($s->value . ' ' . $s->unit),
            ]),
            'rams' => RamOption::orderBy('value')->get()->map(fn($r) => [
                'id' => $r->id,
                'label' => trim($r->value . ' ' . ($r->unit ?? 'GB')),
            ]),
        ]);
    }

    public function edit(Product $product)
    {
        $product->load(['colors:id', 'category:id,name', 'brand:id,name']);

        $brands = Brand::with('categories:id')->get()->map(fn($b) => [
            'id' => $b->id,
            'name' => $b->name,
            'category_ids' => $b->categories->pluck('id')->values(),
        ]);

        // ✅ legacy fallback for old single values
        $storageIds = is_array($product->storage_option_ids) && count($product->storage_option_ids)
            ? $product->storage_option_ids
            : ($product->storage_option_id ? [(int)$product->storage_option_id] : []);

        $ramIds = is_array($product->ram_option_ids) && count($product->ram_option_ids)
            ? $product->ram_option_ids
            : ($product->ram_option_id ? [(int)$product->ram_option_id] : []);

        return Inertia::render('Products/CreateUpdate', [
            'mode' => 'edit',
            'product' => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'brand_id' => $product->brand_id,
                'model' => $product->model,
                'device_status' => $product->device_status,
                'price_lkr' => (float) $product->price_lkr,

                'discount_type' => $product->discount_type,
                'discount_value' => $product->discount_value,
                'in_stock' => (bool) $product->in_stock,
                'stock_count' => $product->stock_count,
                'status' => $product->status,
                'is_featured' => (bool) $product->is_featured,
                'warranty_option_id' => $product->warranty_option_id,
                'warranty_period' => $product->warranty_period,

                'os' => $product->os,

                // ✅ multi
                'storage_option_ids' => array_values(array_map('intval', $storageIds)),
                'ram_option_ids' => array_values(array_map('intval', $ramIds)),

                // legacy (optional, kept for safety)
                'storage_option_id' => $product->storage_option_id,
                'ram_option_id' => $product->ram_option_id,

                'display_size' => $product->display_size,
                'display_type' => $product->display_type,
                'resolution' => $product->resolution,
                'rear_camera' => $product->rear_camera,
                'front_camera' => $product->front_camera,
                'connectivity' => $product->connectivity,
                'battery_mah' => $product->battery_mah,

                'short_description' => $product->short_description,
                'long_description' => $product->long_description,

                'color_ids' => $product->colors->pluck('id')->values(),
                'main_image_url' => $product->main_image_url,
                'gallery_urls' => $product->gallery_urls,
            ],
            'categories' => Category::select('id','name')->orderBy('name')->get(),
            'brands' => $brands,
            'colors' => ColorOption::select('id','name')->get()->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'image_url' => $c->image_url,
            ]),
            'warranties' => WarrantyOption::select('id','name')->orderBy('name')->get(),
            'storages' => StorageOption::orderBy('value')->get()->map(fn($s) => [
                'id' => $s->id,
                'label' => trim($s->value . ' ' . $s->unit),
            ]),
            'rams' => RamOption::orderBy('value')->get()->map(fn($r) => [
                'id' => $r->id,
                'label' => trim($r->value . ' ' . ($r->unit ?? 'GB')),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        $mainPath = null;
        if ($request->hasFile('main_image')) {
            $mainPath = $request->file('main_image')->store('products', 'public');
        }

        $galleryPaths = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $img) {
                $galleryPaths[] = $img->store('products/gallery', 'public');
            }
        }

        $p = Product::create([
            ...$validated,
            'main_image_path' => $mainPath,
            'gallery_image_paths' => $galleryPaths ?: null,
            'stock_count' => ($validated['in_stock'] ?? true) ? ($validated['stock_count'] ?? null) : null,
        ]);

        $p->colors()->sync($request->input('color_ids', []));

        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);

        if ($request->hasFile('main_image')) {
            if ($product->main_image_path && Storage::disk('public')->exists($product->main_image_path)) {
                Storage::disk('public')->delete($product->main_image_path);
            }
            $product->main_image_path = $request->file('main_image')->store('products', 'public');
        }

        $clearGallery = (bool) $request->input('clear_gallery', false);

        if ($clearGallery) {
            foreach (($product->gallery_image_paths ?: []) as $p) {
                if ($p && Storage::disk('public')->exists($p)) Storage::disk('public')->delete($p);
            }
            $product->gallery_image_paths = null;
        }

        if ($request->hasFile('gallery_images')) {
            foreach (($product->gallery_image_paths ?: []) as $p) {
                if ($p && Storage::disk('public')->exists($p)) Storage::disk('public')->delete($p);
            }

            $paths = [];
            foreach ($request->file('gallery_images') as $img) {
                $paths[] = $img->store('products/gallery', 'public');
            }
            $product->gallery_image_paths = $paths;
        }

        $product->fill([
            ...$validated,
            'stock_count' => ($validated['in_stock'] ?? true) ? ($validated['stock_count'] ?? null) : null,
        ]);

        $product->save();
        $product->colors()->sync($request->input('color_ids', []));

        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->main_image_path && Storage::disk('public')->exists($product->main_image_path)) {
            Storage::disk('public')->delete($product->main_image_path);
        }

        foreach (($product->gallery_image_paths ?: []) as $p) {
            if ($p && Storage::disk('public')->exists($p)) Storage::disk('public')->delete($p);
        }

        $product->colors()->detach();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'model' => ['required', 'string', 'max:255'],
            'device_status' => ['required', 'in:used,brandnew'],
            'price_lkr' => ['required', 'numeric', 'min:0'],

            'discount_type' => ['nullable', 'in:percent,price'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

            'in_stock' => ['nullable', 'boolean'],
            'stock_count' => ['nullable', 'integer', 'min:0'],

            'status' => ['nullable', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],

            'warranty_option_id' => ['nullable', 'exists:warranty_options,id'],
            'warranty_period' => ['nullable', 'string', 'max:100'],

            'os' => ['nullable', 'string', 'max:255'],

            // ✅ NEW (multi)
            'storage_option_ids' => ['nullable', 'array'],
            'storage_option_ids.*' => ['integer', 'exists:storage_options,id'],

            'ram_option_ids' => ['nullable', 'array'],
            'ram_option_ids.*' => ['integer', 'exists:ram_options,id'],

            'display_size' => ['nullable', 'string', 'max:255'],
            'display_type' => ['nullable', 'string', 'max:255'],
            'resolution' => ['nullable', 'string', 'max:255'],
            'rear_camera' => ['nullable', 'string', 'max:255'],
            'front_camera' => ['nullable', 'string', 'max:255'],
            'connectivity' => ['nullable', 'string', 'max:255'],
            'battery_mah' => ['nullable', 'integer', 'min:0'],

            'short_description' => ['nullable', 'string'],
            'long_description' => ['nullable', 'string'],

            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer', 'exists:color_options,id'],

            'main_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'clear_gallery' => ['nullable', 'boolean'],
        ]);
    }
}
