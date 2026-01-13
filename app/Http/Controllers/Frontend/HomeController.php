<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->latest()
            ->paginate(12)
            ->through(function ($product) {

                // ✅ robust image fallback (works even if main_image_url accessor is missing)
                $image = $product->main_image_url
                    ?? ($product->main_image_path ? asset('storage/' . $product->main_image_path) : null);

                return [
                    'id' => $product->id,
                    'name' => $product->model ?? '',
                    'price' => (float) ($product->price_lkr ?? 0),
                    'image' => $image,
                    'created_at' => optional($product->created_at)->toIso8601String(),
                ];
            });

        return Inertia::render('Frontend/Home', [
            'products' => $products,
        ]);
    }
}
