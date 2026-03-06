<?php

namespace App\Http\Controllers\Admin\Shoes;

use App\Http\Controllers\Controller;
use App\Support\ShoeCatalogData;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShoeProductController extends Controller
{
    public function index()
    {
        return Inertia::render('Shoes/Products/index');
    }

    public function create()
    {
        return Inertia::render('Shoes/Products/partials/CreateUpdate', [
            'mode' => 'create',
            'product' => null,
        ]);
    }

    public function edit(int $product)
    {
        return Inertia::render('Shoes/Products/partials/CreateUpdate', [
            'mode' => 'edit',
            'product' => ShoeCatalogData::product($product),
        ]);
    }

    public function store(Request $request)
    {
        $this->validateProduct($request);

        return redirect()
            ->route('admin.shoes.products.index')
            ->with('success', 'Shoe product saved.');
    }

    public function update(Request $request, int $product)
    {
        $this->validateProduct($request);

        return redirect()
            ->route('admin.shoes.products.index')
            ->with('success', "Shoe product {$product} updated.");
    }

    protected function validateProduct(Request $request): void
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', 'integer'],
            'product_type_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'subcategory_id' => ['required', 'integer'],
            'sku' => ['nullable', 'string', 'max:255'],
            'barcode' => ['nullable', 'string', 'max:255'],
            'model_code' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'full_description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,published,out_of_stock,archived'],
            'featured' => ['nullable', 'boolean'],
            'new_arrival' => ['nullable', 'boolean'],
            'best_seller' => ['nullable', 'boolean'],
            'regular_price' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:20'],
            'tax_class' => ['nullable', 'string', 'max:100'],
            'discount_type' => ['nullable', 'in:percentage,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'sale_start_date' => ['nullable', 'date'],
            'sale_end_date' => ['nullable', 'date'],
            'stock_quantity' => ['nullable', 'integer', 'min:0'],
            'low_stock_alert_quantity' => ['nullable', 'integer', 'min:0'],
            'stock_status' => ['required', 'in:in_stock,out_of_stock,preorder'],
            'gender' => ['nullable', 'in:men,women,kids,unisex'],
            'age_group' => ['nullable', 'in:adult,teen,kids,baby'],
            'size_type_ids' => ['required', 'array', 'min:1'],
            'size_type_ids.*' => ['integer'],
            'sizes_by_type' => ['required', 'array', 'min:1'],
            'sizes_by_type.*.type' => ['required', 'string', 'max:50'],
            'sizes_by_type.*.sizes' => ['required', 'array', 'min:1'],
            'sizes_by_type.*.sizes.*' => ['required', 'string', 'max:50'],
            'color_ids' => ['nullable', 'array'],
            'color_ids.*' => ['integer'],
            'material_ids' => ['nullable', 'array'],
            'material_ids.*' => ['integer'],
            'shoe_weight' => ['nullable', 'string', 'max:100'],
            'thumbnail_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'product_video_url' => ['nullable', 'url', 'max:2048'],
        ]);
    }
}