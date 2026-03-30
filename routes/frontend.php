<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\CartPageController;
use App\Http\Controllers\Frontend\ContactUsController;
use App\Http\Controllers\Frontend\CheckoutPageController;
use App\Http\Controllers\Frontend\ProductSuggestionController;
use App\Http\Controllers\Frontend\WebShoeProductController;
use App\Http\Controllers\Frontend\WebTechProductController;
use App\Http\Controllers\Frontend\TechProductViewController;
use App\Http\Controllers\Frontend\ShoeProductViewController;

Route::get('/', [HomeController::class, 'index'])->name('frontend.root');

Route::get('/contact-us', [ContactUsController::class, 'index'])
    ->name('frontend.contact-us.index');

Route::get('/cart', [CartPageController::class, 'index'])
    ->name('frontend.cart.index');

Route::get('/checkout', [CheckoutPageController::class, 'index'])
    ->name('frontend.checkout.index');

Route::post('/checkout/place-order', [CheckoutPageController::class, 'store'])
    ->name('frontend.checkout.store');

Route::get('/home/categories', [HomeController::class, 'categories'])
    ->name('frontend.home.categories');

Route::get('/home/shoe-categories', [HomeController::class, 'shoeCategories'])
    ->name('frontend.home.shoe-categories');

Route::get('/home/featured-shoes', [HomeController::class, 'featuredShoes'])
    ->name('frontend.home.featured-shoes');

Route::get('/home/products', [HomeController::class, 'products'])
    ->name('frontend.home.products');

Route::get('/tech-products', [WebTechProductController::class, 'index'])
    ->name('frontend.tech-products.index');

Route::get('/tech-products/products', [WebTechProductController::class, 'products'])
    ->name('frontend.tech-products.products');

Route::get('/tech-products/related/cart', [WebTechProductController::class, 'cartRelated'])
    ->name('frontend.tech-products.cart-related');

Route::get('/tech-products/{product}', [TechProductViewController::class, 'index'])
    ->name('frontend.tech-products.show');

Route::get('/tech-products/{product}/data', [TechProductViewController::class, 'data'])
    ->name('frontend.tech-products.show.data');

Route::get('/shoe-products', [WebShoeProductController::class, 'index'])
    ->name('frontend.shoe-products.index');

Route::get('/shoe-products/products', [WebShoeProductController::class, 'products'])
    ->name('frontend.shoe-products.products');

Route::get('/shoe-products/{product}', [ShoeProductViewController::class, 'index'])
    ->name('frontend.shoe-products.show');

Route::get('/shoe-products/{product}/data', [ShoeProductViewController::class, 'data'])
    ->name('frontend.shoe-products.show.data');

Route::get('/search/product-suggestions', [ProductSuggestionController::class, 'suggestions'])
    ->name('frontend.products.suggestions');

Route::redirect('/home', '/')->name('frontend.home');
