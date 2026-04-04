<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CosmeticProduct;
use App\Models\CosmeticProductVariant;
use App\Models\CosmeticSizeVolume;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Schema;

class CosmeticProductViewController extends Controller
{
    public function index(string $product): Response
    {
        $cosmetic = $this->findProduct($product);

        abort_if(!$cosmetic || !in_array($cosmetic->status, ['published', 'active']), 404);

        return Inertia::render('Frontend/cosmetic_productview/index', [
            'productKey' => $product,
            'shell' => [
                'name' => $cosmetic->name,
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Cosmetics', 'href' => '/cosmetic-products'],
                    ['label' => $cosmetic->name, 'href' => null],
                ],
            ],
        ]);
    }

    public function data(Request $request, string $product): JsonResponse
    {
        $product = $this->findProduct($product);

        abort_if(!$product || !in_array($product->status, ['published', 'active']), 404);

        $product->load(['brand', 'category', 'productType', 'countryOfOrigin', 'variants.sizeVolume']);

        $regularPrice = $product->price !== null ? (float) $product->price : null;

        $variants = $product->variants->map(function (CosmeticProductVariant $variant) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => $variant->price !== null ? (float) $variant->price : null,
                'stock_count' => $variant->stock_count,
                'size_volume_id' => $variant->cosmetic_size_volume_id,
                'size_display' => $variant->sizeVolume?->display,
                'status' => $variant->status,
            ];
        })->values();

        $volumes = CosmeticSizeVolume::query()
            ->whereIn('id', $product->size_volume_ids ?? [])
            ->get()
            ->map(function (CosmeticSizeVolume $v) {
                return [
                    'id' => $v->id,
                    'display' => $v->display,
                ];
            })->values();

        $gallery = $product->gallery_urls ?? [];

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

        $payload = [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'product_type' => $product->productType?->name,
                'country_of_origin' => $product->countryOfOrigin?->name,
                'thumbnail_url' => $product->main_image_url,
                'gallery' => $gallery,
                'regular_price' => $regularPrice,
                'display_price' => $displayPrice,
                'has_discount' => $hasDiscount,
                'discount_label' => $discountLabel,
                'is_sold_out' => $isSoldOut,
                'stock' => $product->stock,
                'short_description' => $product->short_description,
                'long_description' => $product->long_description,
                'volumes' => $volumes,
                'variants' => $variants,
                'reviews_count' => (int) ($product->reviews()->count() ?? 0),
                'reviews_avg_rating' => $product->reviews()->avg('rating') ?? null,
            ],
            'related_products' => [],
        ];

        return response()->json($payload);
    }

    public function reviews(Request $request, string $product): JsonResponse
    {
        $product = $this->findProduct($product);

        abort_if(!$product || !in_array($product->status, ['published', 'active']), 404);

        $reviews = $product->reviews()->latest('id')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at?->toDateTimeString(),
            ];
        })->values();

        return response()->json([
            'reviews' => $reviews,
        ]);
    }

    private function findProduct(string $identifier): ?CosmeticProduct
    {
        $query = CosmeticProduct::query();

        if (ctype_digit((string) $identifier)) {
            return $query->where('id', (int) $identifier)->first();
        }

        // prefer slug when available
        if (Schema::hasColumn('cosmetic_products', 'slug')) {
            return $query->where('slug', $identifier)->first();
        }

        // fallback: try case-insensitive name match
        if (Schema::hasColumn('cosmetic_products', 'name')) {
            return $query->whereRaw('LOWER(name) = ?', [mb_strtolower($identifier)])->first();
        }

        return null;
    }
}
