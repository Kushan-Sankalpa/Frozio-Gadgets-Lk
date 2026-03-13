<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColorOption;
use App\Models\HomeBanner;
use App\Models\Product;
use App\Models\ShoeCategory;
use App\Models\ShoeProduct;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        $activeCategory = request('category');
        $activeShoeCategory = request('shoe_category');
        $activeShoeSubcategory = request('shoe_subcategory');
        $search = trim((string) request('search', ''));

        $normalizedCategory = filled($activeCategory)
            ? mb_strtolower(trim((string) $activeCategory))
            : null;

        $normalizedShoeCategory = filled($activeShoeCategory)
            ? mb_strtolower(trim((string) $activeShoeCategory))
            : null;

        $normalizedShoeSubcategory = filled($activeShoeSubcategory)
            ? mb_strtolower(trim((string) $activeShoeSubcategory))
            : null;

        $normalizedSearch = filled($search)
            ? mb_strtolower($search)
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

        $shoeCategories = ShoeCategory::query()
            ->where('status', 'active')
            ->with([
                'subcategories' => function ($query) {
                    $query->select('id', 'category_id', 'name')
                        ->orderBy('name');
                },
            ])
            ->oldest('id')
            ->get()
            ->map(function (ShoeCategory $category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'image_url' => $category->image_url,
                    'status' => $category->status,
                    'subcategories' => $category->subcategories
                        ->map(fn ($subcategory) => [
                            'id' => $subcategory->id,
                            'name' => $subcategory->name,
                        ])
                        ->values(),
                ];
            })
            ->values();

        $today = now()->startOfDay();

        $featuredShoes = ShoeProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
            ])
            ->where('featured', true)
            ->where('status', 'published')
            ->when($normalizedShoeCategory, function ($query) use ($normalizedShoeCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedShoeCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedShoeCategory]);
                });
            })
            ->when($normalizedShoeSubcategory, function ($query) use ($normalizedShoeSubcategory) {
                $query->whereHas('subcategory', function ($subcategoryQuery) use ($normalizedShoeSubcategory) {
                    $subcategoryQuery->whereRaw('LOWER(name) = ?', [$normalizedShoeSubcategory]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(slug) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                });
            })
            ->latest('id')
            ->take(8)
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

        $baseProducts = Product::query()
            ->with('category:id,name')
            ->where('status', 'active')
            ->when($normalizedCategory, function ($query, $normalizedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($normalizedCategory) {
                    $categoryQuery->whereRaw('LOWER(name) = ?', [$normalizedCategory]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(model) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                });
            })
            ->latest('id')
            ->take(4)
            ->get();

        $colorIds = $baseProducts
            ->flatMap(function (Product $product) {
                return collect($product->color_ids ?? [])->map(fn ($id) => (int) $id);
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $colorMap = ColorOption::query()
            ->select('id', 'name', 'image_path')
            ->whereIn('id', $colorIds)
            ->get()
            ->keyBy('id');

        $products = $baseProducts
            ->map(function (Product $product) use ($colorMap) {
                $regularPrice = (float) ($product->price_lkr ?? 0);
                $displayPrice = $regularPrice;
                $discountLabel = null;

                $discountType = $product->discount_type;
                $discountValue = $product->discount_value !== null ? (float) $product->discount_value : null;

                if ($discountValue !== null && $discountValue > 0 && $regularPrice > 0) {
                    if ($discountType === 'percent') {
                        $displayPrice = max(0, round($regularPrice - (($regularPrice * $discountValue) / 100), 2));
                        $discountLabel = 'Sale ' . rtrim(rtrim(number_format($discountValue, 2), '0'), '.') . '%';
                    } elseif ($discountType === 'price') {
                        $displayPrice = max(0, round($regularPrice - $discountValue, 2));
                        $discountLabel = 'Sale Rs ' . number_format($discountValue, 0);
                    }
                }

                $hasDiscount = $displayPrice < $regularPrice;

                if (!$hasDiscount) {
                    $displayPrice = $regularPrice;
                    $discountLabel = null;
                }

                $isSoldOut = !$product->in_stock
                    || ($product->stock_count !== null && (int) $product->stock_count <= 0);

                $colors = collect($product->color_ids ?? [])
                    ->map(function ($colorId) use ($colorMap) {
                        $color = $colorMap->get((int) $colorId);

                        if (!$color) {
                            return null;
                        }

                        return [
                            'id' => $color->id,
                            'name' => $color->name,
                            'image_url' => $color->image_url,
                        ];
                    })
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'id' => $product->id,
                    'name' => $product->model ?? '',
                    'category_name' => $product->category?->name,
                    'thumbnail_url' => $product->main_image_url,
                    'hover_image_url' => $product->hover_image_url,
                    'regular_price' => $regularPrice,
                    'display_price' => $displayPrice,
                    'has_discount' => $hasDiscount,
                    'discount_label' => $discountLabel,
                    'is_sold_out' => $isSoldOut,
                    'colors' => $colors,
                ];
            })
            ->values();

        return Inertia::render('Frontend/Home/index', [
            'products' => $products,
            'activeCategory' => $activeCategory,
            'banners' => $banners,
            'categories' => [],
            'shoeCategories' => $shoeCategories,
            'featuredShoes' => $featuredShoes,
            'search' => $search,
            'activeShoeCategory' => $activeShoeCategory,
            'activeShoeSubcategory' => $activeShoeSubcategory,
        ]);
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->where('status', 'active')
            ->oldest('id')
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

        return response()->json([
            'categories' => $categories,
        ]);
    }
}