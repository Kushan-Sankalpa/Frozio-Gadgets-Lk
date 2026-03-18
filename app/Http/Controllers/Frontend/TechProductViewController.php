<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ColorOption;
use App\Models\Product;
use App\Models\StorageOption;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class TechProductViewController extends Controller
{
    public function index(Product $product): Response
    {
        abort_if($product->status !== 'active', 404);

        return Inertia::render('techproduct_view/index', [
            'productId' => $product->id,
            'shell' => [
                'name' => $product->model,
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Tech Products', 'href' => '/tech-products'],
                    ['label' => $product->model, 'href' => null],
                ],
            ],
        ]);
    }

    public function data(Product $product): JsonResponse
    {
        abort_if($product->status !== 'active', 404);

        $product->load([
            'category',
            'brand',
            'warrantyOption',
            'colors',
            'variants',
        ]);

        $storageIds = collect($product->storage_option_ids ?: [])
            ->merge($product->storage_option_id ? [$product->storage_option_id] : [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $colorIds = collect($product->color_ids ?: [])
            ->merge($product->colors->pluck('id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $storageOptions = StorageOption::query()
            ->whereIn('id', $storageIds)
            ->orderBy('value')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'label' => trim($item->value . ' ' . $item->unit),
            ])
            ->values();

        $colorOptions = ColorOption::query()
            ->whereIn('id', $colorIds)
            ->orderBy('name')
            ->get()
            ->map(function ($item) use ($product) {
                $relatedColor = $product->colors->firstWhere('id', $item->id);

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'color_code' => $item->color_code,
                    'image_url' => $relatedColor?->image_url ?? $item->image_url,
                ];
            })
            ->values();

        $variantRows = $product->variants
            ->map(function ($variant) use ($product) {
                $price = (float) ($variant->price_lkr ?: $product->price_lkr ?: 0);
                $discount = $this->resolveDiscount($price, $product->discount_type, $product->discount_value);

                return [
                    'id' => $variant->id,
                    'color_option_id' => $variant->color_option_id,
                    'storage_option_id' => $variant->storage_option_id,
                    'sku' => $variant->sku,
                    'price_lkr' => $price,
                    'old_price_lkr' => $discount['old_price'],
                    'final_price_lkr' => $discount['current_price'],
                    'stock_count' => (int) ($variant->stock_count ?? 0),
                    'in_stock' => (int) ($variant->stock_count ?? 0) > 0 && $variant->status !== 'inactive',
                    'status' => $variant->status,
                    'discount_label' => $discount['label'],
                ];
            })
            ->values();

        if ($variantRows->isEmpty()) {
            $discount = $this->resolveDiscount((float) ($product->price_lkr ?: 0), $product->discount_type, $product->discount_value);

            $variantRows = collect([[
                'id' => 'default',
                'color_option_id' => $colorOptions->first()['id'] ?? null,
                'storage_option_id' => $storageOptions->first()['id'] ?? null,
                'sku' => $product->sku,
                'price_lkr' => (float) ($product->price_lkr ?: 0),
                'old_price_lkr' => $discount['old_price'],
                'final_price_lkr' => $discount['current_price'],
                'stock_count' => (int) ($product->stock_count ?? 0),
                'in_stock' => (bool) $product->in_stock,
                'status' => 'active',
                'discount_label' => $discount['label'],
            ]]);
        }

        $displayVariant = $variantRows->firstWhere('in_stock', true) ?: $variantRows->first();

        $specifications = collect([
            ['label' => 'Operating System', 'value' => $product->os],
            ['label' => 'Display Size', 'value' => $product->display_size],
            ['label' => 'Display Type', 'value' => $product->display_type],
            ['label' => 'Resolution', 'value' => $product->resolution],
            ['label' => 'Rear Camera', 'value' => $product->rear_camera],
            ['label' => 'Front Camera', 'value' => $product->front_camera],
            ['label' => 'Connectivity', 'value' => $product->connectivity],
            ['label' => 'Battery', 'value' => $product->battery_mah ? $product->battery_mah . ' mAh' : null],
            ['label' => 'Device Status', 'value' => ucfirst((string) $product->device_status)],
            ['label' => 'Warranty', 'value' => $this->warrantyLabel($product)],
            ['label' => 'Category', 'value' => $product->category?->name],
            ['label' => 'Brand', 'value' => $product->brand?->name],
            ['label' => 'SKU', 'value' => $product->sku],
        ])
            ->filter(fn ($row) => filled($row['value']))
            ->values();

        $gallery = collect([
            ['id' => 'main', 'src' => $product->main_image_url, 'color_option_id' => null],
            ['id' => 'hover', 'src' => $product->hover_image_url, 'color_option_id' => null],
            ...collect($product->gallery_urls ?? [])->map(fn ($url, $index) => [
                'id' => 'gallery-' . $index,
                'src' => $url,
                'color_option_id' => null,
            ])->all(),
            ...$colorOptions->filter(fn ($item) => filled($item['image_url']))->map(fn ($item) => [
                'id' => 'color-' . $item['id'],
                'src' => $item['image_url'],
                'color_option_id' => $item['id'],
            ])->all(),
        ])
            ->filter(fn ($row) => filled($row['src']))
            ->unique('src')
            ->values();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->model,
                'sku' => $product->sku,
                'short_description' => $product->short_description,
                'long_description' => $product->long_description,
                'brand' => [
                    'id' => $product->brand?->id,
                    'name' => $product->brand?->name,
                    'logo_url' => $this->brandLogoUrl($product->brand),
                ],
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name,
                ],
                'breadcrumb' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'Tech Products', 'href' => '/tech-products'],
                    ['label' => $product->model, 'href' => null],
                ],
                'gallery' => $gallery,
                'colors' => $colorOptions,
                'storage_options' => $storageOptions,
                'variants' => $variantRows,
                'warranty_label' => $this->warrantyLabel($product),
                'base_price' => (float) ($product->price_lkr ?: 0),
                'old_price' => $displayVariant['old_price_lkr'] ?? null,
                'current_price' => $displayVariant['final_price_lkr'] ?? (float) ($product->price_lkr ?: 0),
                'has_discount' => !empty($displayVariant['old_price_lkr'])
                    && (float) $displayVariant['old_price_lkr'] > (float) ($displayVariant['final_price_lkr'] ?? 0),
                'discount_label' => $displayVariant['discount_label'] ?? null,
                'specifications' => $specifications,
                'default_color_id' => $displayVariant['color_option_id'] ?? ($colorOptions->first()['id'] ?? null),
                'default_storage_id' => $displayVariant['storage_option_id'] ?? ($storageOptions->first()['id'] ?? null),
            ],
        ]);
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

    private function warrantyLabel(Product $product): ?string
    {
        $segments = array_filter([
            $product->warrantyOption?->name,
            $product->warranty_period,
        ]);

        return empty($segments) ? null : implode(' - ', $segments);
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
