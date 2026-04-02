<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\StorageOptionController;
use App\Http\Controllers\RamOptionController;
use App\Http\Controllers\WarrantyOptionController;
use App\Http\Controllers\ColorOptionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeBannerController;
use App\Http\Controllers\Admin\Shoes\ShoeBrandController;
use App\Http\Controllers\Admin\Shoes\ShoeTypeController;
use App\Http\Controllers\Admin\Shoes\ShoeCategoryController;
use App\Http\Controllers\Admin\Shoes\ShoeSubcategoryController;
use App\Http\Controllers\Admin\Shoes\ShoeSizeTypeController;
use App\Http\Controllers\Admin\Shoes\ShoeColorController;
use App\Http\Controllers\Admin\Shoes\ShoeMaterialController;
use App\Http\Controllers\Admin\Shoes\ShoeProductController;
use App\Http\Controllers\InvoiceController;

require __DIR__ . '/frontend.php';

Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacypolicy');

Route::redirect('/dashboard', '/admin/dashboard');
Route::redirect('/login', '/admin/login');

Route::prefix('admin')->middleware('web')->group(function () {

    Route::get('/login', function () {
        return Inertia::render('auth/Login', [
            'status' => session('status'),
            'canRegister' => Features::enabled(Features::registration()),
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
        ]);
    })->middleware('guest')->name('admin.login');

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/data', [BrandController::class, 'data'])->name('brands.data');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('/storage-options', [StorageOptionController::class, 'index'])->name('storage.index');
        Route::get('/storage-options/data', [StorageOptionController::class, 'data'])->name('storage.data');
        Route::get('/storage-options/create', [StorageOptionController::class, 'create'])->name('storage.create');
        Route::post('/storage-options', [StorageOptionController::class, 'store'])->name('storage.store');
        Route::get('/storage-options/{storageOption}/edit', [StorageOptionController::class, 'edit'])->name('storage.edit');
        Route::put('/storage-options/{storageOption}', [StorageOptionController::class, 'update'])->name('storage.update');
        Route::delete('/storage-options/{storageOption}', [StorageOptionController::class, 'destroy'])->name('storage.destroy');

        Route::get('/ram-options', [RamOptionController::class, 'index'])->name('ram.index');
        Route::get('/ram-options/data', [RamOptionController::class, 'data'])->name('ram.data');
        Route::get('/ram-options/create', [RamOptionController::class, 'create'])->name('ram.create');
        Route::post('/ram-options', [RamOptionController::class, 'store'])->name('ram.store');
        Route::get('/ram-options/{ramOption}/edit', [RamOptionController::class, 'edit'])->name('ram.edit');
        Route::put('/ram-options/{ramOption}', [RamOptionController::class, 'update'])->name('ram.update');
        Route::delete('/ram-options/{ramOption}', [RamOptionController::class, 'destroy'])->name('ram.destroy');

        Route::get('/warranty-options', [WarrantyOptionController::class, 'index'])->name('warranty.index');
        Route::get('/warranty-options/data', [WarrantyOptionController::class, 'data'])->name('warranty.data');
        Route::get('/warranty-options/create', [WarrantyOptionController::class, 'create'])->name('warranty.create');
        Route::post('/warranty-options', [WarrantyOptionController::class, 'store'])->name('warranty.store');
        Route::get('/warranty-options/{warrantyOption}/edit', [WarrantyOptionController::class, 'edit'])->name('warranty.edit');
        Route::put('/warranty-options/{warrantyOption}', [WarrantyOptionController::class, 'update'])->name('warranty.update');
        Route::delete('/warranty-options/{warrantyOption}', [WarrantyOptionController::class, 'destroy'])->name('warranty.destroy');

        Route::get('/color-options', [ColorOptionController::class, 'index'])->name('colors.index');
        Route::get('/color-options/data', [ColorOptionController::class, 'data'])->name('colors.data');
        Route::get('/color-options/create', [ColorOptionController::class, 'create'])->name('colors.create');
        Route::post('/color-options', [ColorOptionController::class, 'store'])->name('colors.store');
        Route::get('/color-options/{colorOption}/image', [ColorOptionController::class, 'image'])->name('colors.image');
        Route::get('/color-options/{colorOption}/edit', [ColorOptionController::class, 'edit'])->name('colors.edit');
        Route::put('/color-options/{colorOption}', [ColorOptionController::class, 'update'])->name('colors.update');
        Route::delete('/color-options/{colorOption}', [ColorOptionController::class, 'destroy'])->name('colors.destroy');

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/data', [ProductController::class, 'data'])->name('products.data');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::prefix('shoes')->name('admin.shoes.')->group(function () {
            Route::prefix('brands')->name('brands.')->controller(ShoeBrandController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{brand}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{brand}', 'update')->name('update');
                Route::delete('/{brand}', 'destroy')->name('destroy');
            });

            Route::prefix('types')->name('types.')->controller(ShoeTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{type}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{type}', 'update')->name('update');
                Route::delete('/{type}', 'destroy')->name('destroy');
            });

            Route::prefix('categories')->name('categories.')->controller(ShoeCategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{category}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{category}', 'update')->name('update');
                Route::delete('/{category}', 'destroy')->name('destroy');
            });

            Route::prefix('subcategories')->name('subcategories.')->controller(ShoeSubcategoryController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{subcategory}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{subcategory}', 'update')->name('update');
                Route::delete('/{subcategory}', 'destroy')->name('destroy');
            });

            Route::prefix('size-types')->name('size-types.')->controller(ShoeSizeTypeController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{sizeType}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{sizeType}', 'update')->name('update');
                Route::delete('/{sizeType}', 'destroy')->name('destroy');
            });

            Route::prefix('colors')->name('colors.')->controller(ShoeColorController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{color}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{color}', 'update')->name('update');
                Route::delete('/{color}', 'destroy')->name('destroy');
            });

            Route::prefix('materials')->name('materials.')->controller(ShoeMaterialController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/options', 'options')->name('options');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{material}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{material}', 'update')->name('update');
                Route::delete('/{material}', 'destroy')->name('destroy');
            });

            Route::prefix('products')->name('products.')->controller(ShoeProductController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/data', 'data')->name('data');
                Route::get('/create', 'create')->name('create');
                Route::get('/generate-sku', 'generateSku')->name('generate-sku');
                Route::post('/', 'store')->name('store');
                Route::get('/{product}/edit', 'edit')->name('edit');
                Route::match(['put', 'patch'], '/{product}', 'update')->name('update');
                Route::delete('/{product}', 'destroy')->name('destroy');
            });
        });

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/data', [InvoiceController::class, 'data'])->name('invoices.data');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::post('/invoices/order-status', [InvoiceController::class, 'updateorderstatus'])->name('invoices.order-status');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');

        Route::prefix('other-cms')->group(function () {
            Route::get('/homebanners', [HomeBannerController::class, 'index'])->name('homebanners.index');
            Route::get('/homebanners/create', [HomeBannerController::class, 'create'])->name('homebanners.create');
            Route::post('/homebanners', [HomeBannerController::class, 'store'])->name('homebanners.store');
            Route::get('/homebanners/{homeBanner}/edit', [HomeBannerController::class, 'edit'])->name('homebanners.edit');
            Route::put('/homebanners/{homeBanner}', [HomeBannerController::class, 'update'])->name('homebanners.update');
            Route::delete('/homebanners/{homeBanner}', [HomeBannerController::class, 'destroy'])->name('homebanners.destroy');
            Route::get('/homebanners/data', [HomeBannerController::class, 'data'])->name('homebanners.data');
        });
    });
});
