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
        $shoe = $this->resolveProduct($product);

        abort_if($shoe->status !== 'active', 404);

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
        $shoe = $this->resolveProduct($product);

        abort_if($shoe->status !== 'active', 404);

        $shoe->load([
            'brand',
            'category',
            'subcategory',
            'sizes',
            'variants',
        ]);

        $fallbackStock = $shoe->stock_count !== null
            ? (int) $shoe->stock_count
            : ((bool) $shoe->in_stock ? 1 : 0);

        $sizeOptions = $shoe->sizes
            ->map(fn ($size) => [
                'id' => $size->id,
                'label' => $size->label ?? $size->name ?? $size->size ?? '',
            ])
            ->filter(fn ($size) => filled($size['label']))
            ->values();

        if ($sizeOptions->isEmpty()) {
            $sizeOptions = $shoe->variants
                ->map(fn ($variant) => [
                    'id' => $variant->size_id ?? $variant->id,
                    'label' => $variant->size_label ?? $variant->size ?? $variant->name ?? '',
                ])
                ->filter(fn ($size) => filled($size['label']))
                ->unique('label')
                ->values();
        }

        $variantRows = $shoe->variants
            ->map(function ($variant) use ($shoe, $fallbackStock) {
                $basePrice = (float) ($variant->price_lkr ?: $shoe->price_lkr ?: 0);
                $discount = $this->resolveDiscount(
                    $basePrice,
                    $shoe->discount_type,
                    $shoe->discount_value
                );

                $resolvedStock = $variant->stock_count !== null
                    ? (int) $variant->stock_count
                    : $fallbackStock;

                $variantStatus = $variant->status ?: 'active';
                $inStock = $variantStatus !== 'inactive' && $resolvedStock > 0;

                return [
                    'id' => $variant->id,
                    'size_id' => $variant->size_id ?? $variant->id,
                    'size_label' => $variant->size_label ?? $variant->size ?? null,
                    'sku' => $variant->sku,
                    'price_lkr' => $basePrice,
                    'old_price_lkr' => $discount['old_price'],
                    'final_price_lkr' => $discount['current_price'],
                    'stock_count' => $resolvedStock,
                    'in_stock' => $inStock,
                    'status' => $variantStatus,
                    'discount_label' => $discount['label'],
                ];
            })
            ->values();

        if ($variantRows->isEmpty()) {
            $discount = $this->resolveDiscount(
                (float) ($shoe->price_lkr ?: 0),
                $shoe->discount_type,
                $shoe->discount_value
            );

            $variantRows = collect([[
                'id' => 'default',
                'size_id' => $sizeOptions->first()['id'] ?? null,
                'size_label' => $sizeOptions->first()['label'] ?? null,
                'sku' => $shoe->sku,
                'price_lkr' => (float) ($shoe->price_lkr ?: 0),
                'old_price_lkr' => $discount['old_price'],
                'final_price_lkr' => $discount['current_price'],
                'stock_count' => $fallbackStock,
                'in_stock' => ((bool) $shoe->in_stock) && $fallbackStock > 0,
                'status' => 'active',
                'discount_label' => $discount['label'],
            ]]);
        }

        $displayVariant = $variantRows->first(fn ($variant) => ($variant['in_stock'] ?? false) === true)
            ?: $variantRows->first(fn ($variant) => ($variant['status'] ?? 'active') !== 'inactive')
            ?: $variantRows->first();

        $gallery = collect($shoe->gallery_urls ?? [])
            ->filter()
            ->unique()
            ->take(4)
            ->values()
            ->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
            ])
            ->values();

        $mainImage = $shoe->main_image_url ?: ($gallery->first()['src'] ?? null);

        return response()->json([
            'product' => [
                'id' => $shoe->id,
                'name' => $shoe->name,
                'slug' => $shoe->slug,
                'sku' => $shoe->sku,
                'short_description' => $shoe->short_description,
                'long_description' => $shoe->long_description,
                'brand' => [
                    'id' => $shoe->brand?->id,
                    'name' => $shoe->brand?->name,
                    'logo_url' => $this->brandLogoUrl($shoe->brand),
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
                'sizes' => $sizeOptions,
                'variants' => $variantRows,
                'size_chart_image' => $shoe->size_chart_url
                    ?? $shoe->size_chart_image_url
                    ?? $shoe->chart_image_url
                    ?? null,
                'base_price' => (float) ($shoe->price_lkr ?: 0),
                'old_price' => $displayVariant['old_price_lkr'] ?? null,
                'current_price' => $displayVariant['final_price_lkr'] ?? (float) ($shoe->price_lkr ?: 0),
                'has_discount' => !empty($displayVariant['old_price_lkr'])
                    && (float) $displayVariant['old_price_lkr'] > (float) ($displayVariant['final_price_lkr'] ?? 0),
                'discount_label' => $displayVariant['discount_label'] ?? null,
                'default_size_id' => $displayVariant['size_id'] ?? ($sizeOptions->first()['id'] ?? null),
                'stock_count' => $displayVariant['stock_count'] ?? $fallbackStock,
                'in_stock' => $displayVariant['in_stock'] ?? (((bool) $shoe->in_stock) && $fallbackStock > 0),
            ],
        ]);
    }

    private function resolveProduct(string $identifier): ShoeProduct
    {
        return ShoeProduct::query()
            ->when(
                is_numeric($identifier),
                fn ($query) => $query->where('id', (int) $identifier)->orWhere('slug', $identifier),
                fn ($query) => $query->where('slug', $identifier)
            )
            ->firstOrFail();
    }

    private function resolveDiscount(float $price, ?string $discountType, $discountValue): array
    {
        $price = max(0, round($price, 2));
        $discountValue = is_numeric($discountValue) ? (float) $discountValue : 0.0;

        if ($discountValue <= 0 || !in_array($discountType, ['percent', 'price'], true)) {
            return [
                'old_price' => null,
                'current_price' => $price,
                'has_discount' => false,
                'label' => null,
            ];
        }

        if ($discountType === 'percent') {
            $currentPrice = max(0, round($price - (($price * $discountValue) / 100), 2));
            $label = rtrim(rtrim(number_format($discountValue, 2, '.', ''), '0'), '.') . '% OFF';
        } else {
            $currentPrice = max(0, round($price - $discountValue, 2));
            $label = 'Save Rs ' . number_format($discountValue, 2);
        }

        return [
            'old_price' => $currentPrice < $price ? $price : null,
            'current_price' => $currentPrice,
            'has_discount' => $currentPrice < $price,
            'label' => $currentPrice < $price ? $label : null,
        ];
    }

    private function brandLogoUrl($brand): ?string
    {
        if (!$brand) {
            return null;
        }

        return data_get($brand, 'logo_url')
            ?? data_get($brand, 'image_url')
            ?? data_get($brand, 'icon_url');
    }
}