<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;

// Frontend home (public)
Route::get('/', [HomeController::class, 'index'])->name('frontend.root');

// Optional: keep /home working too
Route::redirect('/home', '/')->name('frontend.home');
