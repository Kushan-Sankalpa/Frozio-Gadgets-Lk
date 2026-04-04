<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBrand;
use App\Models\CosmeticProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebCosmeticProductController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Frontend/CosmeticProductList/index', [
            'brands' => $this->brands(),
            'filters' => [
                'search' => trim((string) $request->query('search', '')),
                'brand' => $request->query('brand'),
                'stock' => $request->query('stock'),
                'sale' => $request->boolean('sale'),
                'sort' => $request->query('sort', 'latest'),
                'min_price' => $request->query('min_price'),
                'max_price' => $request->query('max_price'),
                'page' => max(1, (int) $request->query('page', 1)),
            ],
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $activeBrand = $request->query('brand');
        $search = trim((string) $request->query('search', ''));
        $stock = trim((string) $request->query('stock', ''));
        $sort = trim((string) $request->query('sort', 'latest'));
        $saleOnly = $request->boolean('sale');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');

        $normalizedBrand = $this->normalize($activeBrand);
        $normalizedSearch = $this->normalize($search);

        $today = now()->startOfDay();

        $query = CosmeticProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereIn('status', ['published', 'active'])
            ->when($normalizedBrand, function ($query) use ($normalizedBrand) {
                $query->whereHas('brand', function ($brandQuery) use ($normalizedBrand) {
                    $brandQuery->whereRaw('LOWER(name) = ?', [$normalizedBrand]);
                });
            })
            ->when($normalizedSearch, function ($query) use ($normalizedSearch) {
                $query->where(function ($inner) use ($normalizedSearch) {
                    $inner->whereRaw('LOWER(name) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(slug) like ?', ["%{$normalizedSearch}%"])
                        ->orWhereRaw('LOWER(sku) like ?', ["%{$normalizedSearch}%"]);
                });
            })
            ->when($stock === 'in_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->where(function ($q) {
                        $q->where('stock', '>', 0)->orWhereNull('stock');
                    });
                });
            })
            ->when($stock === 'out_of_stock', function ($query) {
                $query->where(function ($inner) {
                    $inner->whereNotNull('stock')->where('stock', '<=', 0);
                });
            });

        switch ($sort) {
            case 'price_low_high':
                $query->orderBy('price', 'asc');
                break;

            case 'price_high_low':
                $query->orderBy('price', 'desc');
                break;

            case 'name_az':
                $query->orderBy('name');
                break;

            case 'name_za':
                $query->orderByDesc('name');
                break;

            default:
                $query->latest('id');
                break;
        }

        $paginator = $query->paginate(12)->withQueryString();

        $products = collect($paginator->items())
            ->map(fn (CosmeticProduct $product) => $this->transformCosmeticProduct($product))
            ->values();

        return response()->json([
            'products' => $products,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem() ?? 0,
                'to' => $paginator->lastItem() ?? 0,
            ],
            'filters' => [
                'search' => $search,
                'brand' => $activeBrand,
                'stock' => $stock,
                'sale' => $saleOnly,
                'sort' => $sort,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
            ],
        ]);
    }

    protected function transformCosmeticProduct(CosmeticProduct $product): array
    {
        $regularPrice = $product->price !== null ? (float) $product->price : null;

        $isSoldOut = $product->status === 'out_of_stock' || ($product->stock !== null && (int) $product->stock <= 0);

        $discountLabel = null;
        $displayPrice = $regularPrice;

        if (!empty($product->discount_type) && $product->discount_value !== null) {
            $val = (float) $product->discount_value;
            if ($product->discount_type === 'percentage' || $product->discount_type === 'percent') {
                $displayPrice = max(0, round($regularPrice - (($regularPrice * $val) / 100), 2));
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format($val, 2), '0'), '.') . '%';
            } else {
                $displayPrice = max(0, round($regularPrice - $val, 2));
                $discountLabel = 'Sale Rs ' . number_format($val, 0);
            }
        }

        $hasDiscount = $displayPrice < $regularPrice;

        if (!$hasDiscount) {
            $displayPrice = $regularPrice;
            $discountLabel = null;
        }

        return [
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'brand_name' => $product->brand?->name,
            'thumbnail_url' => $product->main_image_url,
            'hover_image_url' => $product->main_image_url,
            'currency' => 'LKR',
            'regular_price' => $regularPrice,
            'sale_price' => $hasDiscount ? $displayPrice : null,
            'display_price' => $displayPrice,
            'has_discount' => $hasDiscount,
            'discount_label' => $discountLabel,
            'is_sold_out' => $isSoldOut,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'reviews_avg_rating' => $product->reviews_avg_rating !== null ? (float) $product->reviews_avg_rating : null,
            'status' => $product->status,
        ];
    }

    protected function brands()
    {
        return CosmeticBrand::query()
            ->where('status', 'active')
            ->oldest('id')
            ->get()
            ->map(fn (CosmeticBrand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'logo_url' => $brand->logo_url,
            ])
            ->values();
    }

    protected function normalize($value): ?string
    {
        if (!filled($value)) {
            return null;
        }

        return mb_strtolower(trim((string) $value));
    }
}
