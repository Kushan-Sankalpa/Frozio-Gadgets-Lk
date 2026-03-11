<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Product;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $activeCategory = request('category');
        $normalizedCategory = filled($activeCategory)
            ? mb_strtolower(trim((string) $activeCategory))
            : null;

        $banners = HomeBanner::query()
            ->latest()
            ->get()
            ->map(function (HomeBanner $b) {
                return [
                    'id' => $b->id,
                    'name' => $b->name,
                    'description' => $b->description,
                    'video_url' => $b->video_url,
                ];
            })
            ->values();

        $categories = Category::query()
            ->where('status', 'active')
            ->oldest('id')
            ->take(4)
            ->get()
            ->map(function (Category $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_url' => $category->image_url,
                    'status' => $category->status,
                ];
            })
            ->values();

        $products = Product::query()
            ->with('category:id,name')
            ->when($normalizedCategory, function ($query, $normalizedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(function (Product $product) {
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

        return Inertia::render('Frontend/Home/index', [
            'products' => $products,
            'activeCategory' => $activeCategory,
            'banners' => $banners,
            'categories' => $categories,
        ]);
    }
}