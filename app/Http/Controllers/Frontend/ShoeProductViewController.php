<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ShoeProduct;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShoeProductViewController extends Controller
{
    public function index(string $product): Response
    {
        $shoe = $this->findProduct($product);

        abort_if(!$shoe || $shoe->status !== 'published', 404);

        return Inertia::render('Frontend/shoe_productview/index', [
            'productKey' => $product,
            'shell' => [
                'name' => $shoe->name,
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Shoe Products', 'href' => '/shoe-products'],
                    ['label' => $shoe->name, 'href' => null],
                ],
            ],
        ]);
    }

    public function data(string $product): JsonResponse
    {
        $shoe = $this->findProduct($product);

        abort_if(!$shoe || $shoe->status !== 'published', 404);

        $shoe->load([
            'brand',
            'category',
            'subcategory',
        ]);

        $pricing = $this->resolvePricing($shoe);

        $stockCount = $shoe->stock_quantity !== null ? (int) $shoe->stock_quantity : 0;
        $isInStock = $shoe->stock_status !== 'out_of_stock' && $stockCount > 0;

        $sizes = $this->resolveSizes($shoe);

        $variants = $sizes->isNotEmpty()
            ? $sizes->map(function ($size) use ($shoe, $pricing, $stockCount, $isInStock) {
                return [
                    'id' => 'size-' . $size['id'],
                    'size_id' => $size['id'],
                    'size_label' => $size['label'],
                    'sku' => $shoe->sku,
                    'price_lkr' => $pricing['base_price'],
                    'old_price_lkr' => $pricing['old_price'],
                    'final_price_lkr' => $pricing['current_price'],
                    'stock_count' => $stockCount,
                    'in_stock' => $isInStock,
                    'status' => $isInStock ? 'active' : 'inactive',
                    'discount_label' => $pricing['discount_label'],
                ];
            })->values()
            : collect([[
                'id' => 'default',
                'size_id' => null,
                'size_label' => null,
                'sku' => $shoe->sku,
                'price_lkr' => $pricing['base_price'],
                'old_price_lkr' => $pricing['old_price'],
                'final_price_lkr' => $pricing['current_price'],
                'stock_count' => $stockCount,
                'in_stock' => $isInStock,
                'status' => $isInStock ? 'active' : 'inactive',
                'discount_label' => $pricing['discount_label'],
            ]]);

        $gallery = collect([
            $shoe->thumbnail_url,
            $shoe->hover_image_url,
            ...($shoe->gallery_urls ?? []),
        ])
            ->filter()
            ->unique()
            ->take(6)
            ->values()
            ->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
            ])
            ->values();

        $mainImage = $shoe->thumbnail_url
            ?: $shoe->hover_image_url
            ?: ($gallery->first()['src'] ?? null);

        return response()->json([
            'product' => [
                'id' => $shoe->id,
                'name' => $shoe->name,
                'slug' => $shoe->slug,
                'sku' => $shoe->sku,
                'short_description' => $shoe->short_description,
                'long_description' => $shoe->full_description,
                'brand' => [
                    'id' => $shoe->brand?->id,
                    'name' => $shoe->brand?->name,
                    'logo_url' => data_get($shoe->brand, 'logo_url')
                        ?? data_get($shoe->brand, 'image_url')
                        ?? data_get($shoe->brand, 'icon_url'),
                ],
                'category' => [
                    'id' => $shoe->category?->id,
                    'name' => $shoe->category?->name,
                ],
                'subcategory' => [
                    'id' => $shoe->subcategory?->id,
                    'name' => $shoe->subcategory?->name,
                ],
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Shoe Products', 'href' => '/shoe-products'],
                    ['label' => $shoe->name, 'href' => null],
                ],
                'main_image' => $mainImage,
                'gallery' => $gallery,
                'sizes' => $sizes->values(),
                'variants' => $variants->values(),
                'size_chart_image' => data_get($shoe, 'size_chart_image_url')
                    ?? data_get($shoe, 'size_chart_url')
                    ?? data_get($shoe, 'chart_image_url'),
                'base_price' => $pricing['base_price'],
                'old_price' => $pricing['old_price'],
                'current_price' => $pricing['current_price'],
                'has_discount' => $pricing['has_discount'],
                'discount_label' => $pricing['discount_label'],
                'default_size_id' => $sizes->first()['id'] ?? null,
                'stock_count' => $stockCount,
                'in_stock' => $isInStock,
            ],
            'related_products' => $this->relatedProducts($shoe),
        ]);
    }

    private function findProduct(string $identifier): ?ShoeProduct
    {
        return ShoeProduct::query()
            ->when(
                ctype_digit($identifier),
                fn ($query) => $query->where('id', (int) $identifier)->orWhere('slug', $identifier),
                fn ($query) => $query->where('slug', $identifier)
            )
            ->first();
    }

    private function resolvePricing(ShoeProduct $shoe): array
    {
        $today = now()->startOfDay();

        $regularPrice = $shoe->regular_price !== null ? (float) $shoe->regular_price : 0.0;
        $salePrice = $shoe->sale_price !== null ? (float) $shoe->sale_price : null;

        $saleStarted = !$shoe->sale_start_date || $shoe->sale_start_date->lte($today);
        $saleNotEnded = !$shoe->sale_end_date || $shoe->sale_end_date->gte($today);

        $hasActiveSale = $regularPrice > 0
            && $salePrice !== null
            && $salePrice > 0
            && $regularPrice > $salePrice
            && $saleStarted
            && $saleNotEnded;

        $discountLabel = null;

        if (!empty($shoe->discount_type) && $shoe->discount_value !== null) {
            if ($shoe->discount_type === 'percentage') {
                $discountLabel = 'Sale ' . rtrim(rtrim(number_format((float) $shoe->discount_value, 2), '0'), '.') . '%';
            } elseif ($shoe->discount_type === 'fixed') {
                $discountLabel = 'Sale LKR ' . number_format((float) $shoe->discount_value, 0);
            }
        } elseif ($hasActiveSale) {
            $discountPercent = (int) round((($regularPrice - $salePrice) / $regularPrice) * 100);
            if ($discountPercent > 0) {
                $discountLabel = 'Sale ' . $discountPercent . '%';
            }
        }

        return [
            'base_price' => $regularPrice,
            'old_price' => $hasActiveSale ? $regularPrice : null,
            'current_price' => $hasActiveSale ? $salePrice : $regularPrice,
            'has_discount' => $hasActiveSale,
            'discount_label' => $discountLabel,
        ];
    }

    private function resolveSizes(ShoeProduct $shoe)
    {
        $raw = $shoe->sizes_by_type ?? [];

        return collect($raw)
            ->flatMap(function ($group, $groupIndex) {
                if (!is_array($group)) {
                    return [];
                }

                $typeLabel = trim((string) (
                    $group['type_code']
                    ?? $group['type_name']
                    ?? 'Size'
                ));

                $sizes = $group['sizes'] ?? [];

                return collect($sizes)->map(function ($size, $sizeIndex) use ($group, $groupIndex, $typeLabel) {
                    $sizeValue = '';

                    if (is_array($size)) {
                        $sizeValue = trim((string) (
                            $size['label']
                            ?? $size['name']
                            ?? $size['size']
                            ?? ''
                        ));
                    } else {
                        $sizeValue = trim((string) $size);
                    }

                    if ($sizeValue === '') {
                        return null;
                    }

                    return [
                        'id' => ($group['type_id'] ?? $groupIndex) . '-' . $sizeIndex,
                        'label' => $typeLabel . ' ' . $sizeValue,
                    ];
                });
            })
            ->filter()
            ->unique('label')
            ->values();
    }

    private function relatedProducts(ShoeProduct $shoe)
    {
        if (!$shoe->category_id) {
            return collect([]);
        }

        return ShoeProduct::query()
            ->with(['brand', 'category', 'subcategory'])
            ->where('status', 'published')
            ->where('category_id', $shoe->category_id)
            ->whereKeyNot($shoe->id)
            ->latest('id')
            ->take(4)
            ->get()
            ->map(fn (ShoeProduct $item) => $this->mapRelatedProductCard($item))
            ->values();
    }

    private function mapRelatedProductCard(ShoeProduct $shoe): array
    {
        $shoe->loadMissing(['brand', 'category', 'subcategory']);

        $pricing = $this->resolvePricing($shoe);
        $stockCount = $shoe->stock_quantity !== null ? (int) $shoe->stock_quantity : 0;
        $isSoldOut = $shoe->stock_status === 'out_of_stock' || $stockCount <= 0;

        return [
            'id' => $shoe->id,
            'name' => $shoe->name,
            'slug' => $shoe->slug,
            'brand_name' => $shoe->brand?->name,
            'category_name' => $shoe->category?->name,
            'subcategory_name' => $shoe->subcategory?->name,
            'thumbnail_url' => $shoe->thumbnail_url,
            'hover_image_url' => $shoe->hover_image_url ?: $shoe->thumbnail_url,
            'currency' => 'LKR',
            'regular_price' => $pricing['old_price'],
            'sale_price' => $pricing['has_discount'] ? $pricing['current_price'] : null,
            'display_price' => $pricing['current_price'],
            'has_discount' => $pricing['has_discount'],
            'discount_label' => $pricing['discount_label'],
            'is_sold_out' => $isSoldOut,
            'status' => $shoe->status,
            'stock_status' => $shoe->stock_status,
        ];
    }
}