<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;

// Frontend home (public)
Route::get('/', [HomeController::class, 'index'])->name('frontend.root');

// Categories fetch after homepage loads
Route::get('/home/categories', [HomeController::class, 'categories'])
    ->name('frontend.home.categories');

// Shoe categories fetch only when section is viewed
Route::get('/home/shoe-categories', [HomeController::class, 'shoeCategories'])
    ->name('frontend.home.shoe-categories');

// Products fetch after homepage loads
Route::get('/home/products', [HomeController::class, 'products'])
    ->name('frontend.home.products');

// Optional: keep /home working too
Route::redirect('/home', '/')->name('frontend.home');