<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $activeCategory = request('category'); // used by navbar + UI (no filtering yet)

        $products = Product::query()
            ->latest()
            ->paginate(12)
            ->through(function ($product) {
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

       return Inertia::render('Home/Index', [

            'products' => $products,
            'activeCategory' => $activeCategory,
        ]);
    }
}
