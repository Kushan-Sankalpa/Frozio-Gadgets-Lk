<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ShoeProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductSuggestionController extends Controller
{
    public function suggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $normalizedQuery = $this->normalize($query);
        $likeQuery = '%' . $normalizedQuery . '%';

        $techProducts = Product::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->where('status', 'active')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(model) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(os) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (Product $product) use ($normalizedQuery) {
                return [
                    'id' => 'tech-' . $product->id,
                    'name' => (string) ($product->model ?: 'Product'),
                    'image_url' => $product->main_image_url ?: $product->hover_image_url,
                    'type' => 'tech',
                    'type_label' => 'Tech Product',
                    'target_url' => route('frontend.tech-products.show', [
                        'product' => $product->id,
                    ]),
                    'rank' => $this->rankSuggestion((string) ($product->model ?: ''), $normalizedQuery),
                ];
            });

        $shoeProducts = ShoeProduct::query()
            ->with([
                'brand:id,name',
                'category:id,name',
            ])
            ->where('status', 'published')
            ->where(function ($queryBuilder) use ($likeQuery) {
                $queryBuilder
                    ->whereRaw('LOWER(name) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(slug) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(sku) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(model_code) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(short_description) like ?', [$likeQuery])
                    ->orWhereRaw('LOWER(full_description) like ?', [$likeQuery])
                    ->orWhereHas('brand', function ($brandQuery) use ($likeQuery) {
                        $brandQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    })
                    ->orWhereHas('category', function ($categoryQuery) use ($likeQuery) {
                        $categoryQuery->whereRaw('LOWER(name) like ?', [$likeQuery]);
                    });
            })
            ->limit(8)
            ->get()
            ->map(function (ShoeProduct $product) use ($normalizedQuery) {
                $identifier = filled($product->slug) ? $product->slug : $product->id;

                return [
                    'id' => 'shoe-' . $product->id,
                    'name' => (string) $product->name,
                    'image_url' => $product->thumbnail_url ?: $product->hover_image_url,
                    'type' => 'shoe',
                    'type_label' => 'Shoe Product',
                    'target_url' => route('frontend.shoe-products.show', [
                        'product' => $identifier,
                    ]),
                    'rank' => $this->rankSuggestion((string) $product->name, $normalizedQuery),
                ];
            });

        $results = $techProducts
            ->concat($shoeProducts)
            ->sort(function (array $a, array $b) {
                return [$a['rank'], $a['name']] <=> [$b['rank'], $b['name']];
            })
            ->take(10)
            ->values()
            ->map(function (array $item) {
                unset($item['rank']);
                return $item;
            });

        return response()->json($results);
    }

    private function rankSuggestion(string $name, string $query): int
    {
        $normalizedName = $this->normalize($name);

        if ($query === '') {
            return 999;
        }

        if ($normalizedName === $query) {
            return 0;
        }

        if (str_starts_with($normalizedName, $query)) {
            return 1;
        }

        foreach (preg_split('/\s+/', $normalizedName) ?: [] as $word) {
            if ($word !== '' && str_starts_with($word, $query)) {
                return 2;
            }
        }

        
        if (str_contains($normalizedName, $query)) {
            return 3;
        }

        return 4;
    }

    private function normalize(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }
}