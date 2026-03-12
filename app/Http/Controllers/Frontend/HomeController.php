<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Models\ShoeCategory;
use App\Models\ShoeProduct;
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

            $shoeCategories = ShoeCategory::query()
                ->where('status', 'active')
                ->oldest('id')
                ->take(3)
                ->get()
                ->map(function (ShoeCategory $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image_url' => $category->image_url,
                        'status' => $category->status,
                    ];
                })
                ->values();

            $today = now()->startOfDay();

$featuredShoes = ShoeProduct::query()
    ->with(['brand:id,name'])
    ->where('featured', true)
    ->where('status', 'published')
    ->latest('id')
    ->take(4)
    ->get()
    ->map(function (ShoeProduct $product) use ($today) {
        $regularPrice = $product->regular_price !== null ? (float) $product->regular_price : null;
        $salePrice = $product->sale_price !== null ? (float) $product->sale_price : null;

        $saleStarted = !$product->sale_start_date || $product->sale_start_date->lte($today);
        $saleNotEnded = !$product->sale_end_date || $product->sale_end_date->gte($today);

        $hasActiveSale = $regularPrice !== null
            && $salePrice !== null
            && $salePrice > 0
            && $regularPrice > $salePrice
            && $saleStarted
            && $saleNotEnded;

        $isSoldOut = $product->status === 'out_of_stock'
            || $product->stock_status === 'out_of_stock'
            || ($product->stock_quantity !== null && (int) $product->stock_quantity <= 0);

        $discountLabel = null;

        if (!empty($product->discount_type) && $product->discount_value !== null) {
            if ($product->discount_type === 'percentage') {
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format((float) $product->discount_value, 2), '0'), '.') . '%';
            } elseif ($product->discount_type === 'fixed') {
                $discountLabel = 'Sale LKR ' . number_format((float) $product->discount_value, 0);
            }
        } elseif ($hasActiveSale) {
            $discountPercent = (int) round((($regularPrice - $salePrice) / $regularPrice) * 100);
            if ($discountPercent > 0) {
                $discountLabel = 'Sale ' . $discountPercent . '%';
            }
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $product->thumbnail_url,
            'hover_image_url' => $product->hover_image_url,
            'currency' => $product->currency ?: 'LKR',
            'regular_price' => $regularPrice,
            'sale_price' => $hasActiveSale ? $salePrice : null,
            'display_price' => $hasActiveSale ? $salePrice : $regularPrice,
            'has_discount' => $hasActiveSale,
            'discount_label' => $discountLabel,
            'is_sold_out' => $isSoldOut,
            'status' => $product->status,
            'stock_status' => $product->stock_status,
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
    'shoeCategories' => $shoeCategories,
    'featuredShoes' => $featuredShoes,
]);
    }
}