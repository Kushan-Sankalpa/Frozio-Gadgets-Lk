<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('auth/Login', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacypolicy');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ✅ Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/data', [CategoryController::class, 'data'])->name('categories.data');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // ✅ Brands
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
Route::get('/brands/data', [BrandController::class, 'data'])->name('brands.data');
Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

});
