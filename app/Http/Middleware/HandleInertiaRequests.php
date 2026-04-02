<?php

namespace App\Http\Middleware;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ShoeCategory;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;
use Spatie\Permission\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = Auth::user();
        $shared = [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),

                ] : null,
            ],

            'permission' => $user ? $this->getPermissions($user) : [],

        ];

        if (!$request->is('admin*')) {
            $shared['categories'] = fn () => Category::query()
                ->select('id', 'name', 'image_path', 'status')
                ->where('status', 'active')
                ->with([
                    'brands' => function ($query) {
                        $query->where('brands.status', 'active')
                            ->orderBy('brands.name')
                            ->select('brands.id', 'brands.name', 'brands.logo_path', 'brands.status');
                    }
                ])
                ->oldest('id')
                ->get()
                ->map(function (Category $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image_url' => $category->image_url,
                        'status' => $category->status,
                        'brands' => $category->brands->map(function (Brand $brand) {
                            return [
                                'id' => $brand->id,
                                'name' => $brand->name,
                                'logo_url' => $brand->logo_url,
                                'status' => $brand->status,
                            ];
                        })->values(),
                    ];
                })
                ->values();

            $shared['shoeCategories'] = fn () => ShoeCategory::query()
                ->select('id', 'name', 'image_path', 'status')
                ->where('status', 'active')
                ->with([
                    'subcategories' => function ($query) {
                        $query->where('status', 'active')
                            ->oldest('id')
                            ->select('id', 'category_id', 'name', 'image_path', 'status');
                    }
                ])
                ->oldest('id')
                ->get()
                ->map(function (ShoeCategory $category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'image_url' => $category->image_url,
                        'status' => $category->status,
                        'subcategories' => $category->subcategories->map(function ($subcategory) {
                            return [
                                'id' => $subcategory->id,
                                'name' => $subcategory->name,
                                'image_url' => $subcategory->image_url,
                                'status' => $subcategory->status,
                            ];
                        })->values(),
                    ];
                })
                ->values();
        }

        return $shared;
    }

    private function getPermissions($user): array
    {
        if ($user->hasRole('Super Admin')) {
            return Permission::pluck('name')->mapWithKeys(fn($p) => [$p => true])->toArray();
        }

        return $user->getAllPermissions()
            ->pluck('name')
            ->mapWithKeys(fn($p) => [$p => true])
            ->toArray();
    }
}
