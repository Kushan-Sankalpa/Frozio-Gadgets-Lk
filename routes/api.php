<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\CustomerAuthController;
use App\Http\Controllers\Api\V1\BranchController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\CountryCodeController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\BookingsController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\LoyaltyTierController;

Route::prefix('v1')->group(function () {
    Route::prefix('/auth')->group(function () {
        Route::post('/client-register',[CustomerAuthController::class, 'register']);
        Route::post('/client-login', [CustomerAuthController::class, 'login']);
        Route::post('/login/social/google', [CustomerAuthController::class, 'loginWithGoogle']);
        Route::post('/login/social/apple', [CustomerAuthController::class, 'loginWithApple']);

        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::get('/check-status', [CustomerAuthController::class, 'status']);
            Route::get('/send-welcome-sms',[CustomerAuthController::class, 'sendWelcomeMessage']);
            Route::post('/verify-otp', [CustomerAuthController::class, 'verifyOTP']);
            Route::post('/resend-otp', [CustomerAuthController::class, 'resendOTP']);
            Route::get('/user-details', [CustomerAuthController::class, 'getUserDetails']);
            Route::post('/logout', [CustomerAuthController::class, 'logout']);
            Route::post('/change-password', [CustomerAuthController::class, 'changePassword']);
            Route::post('/save-verified-password', [CustomerAuthController::class, 'saveVerifiedPassword']);
            Route::post('/update-profile', [CustomerAuthController::class, 'updateProfile']);
            Route::post('/soft-delete', [CustomerAuthController::class, 'accountSoftDelete']);
        });
    });
    Route::prefix('/countries')->group(function () {
        Route::get('/get-country-codes', [CountryCodeController::class, 'getCountryCodes']);
    });
    Route::prefix('/banners')->group(function () {
        Route::get('/get-banners', [BannerController::class, 'getBanners']);
    });
    Route::prefix('/discounts')->group(function () {
        Route::get('/get-discounts', [DiscountController::class, 'getDiscounts']);
    });
    Route::prefix('/branches')->group(function () {
        Route::get('/get-branches', [BranchController::class, 'getBranches']);
        Route::post('/get-branches-staff',[BranchController::class, 'getBranchTeam']);
    });
    Route::prefix('/services')->group(function () {
        Route::get('/get-service-categories', [ServiceController::class, 'getServiceCategories']);
        Route::post('/get-services', [ServiceController::class, 'getStaffWithServicesWithCategories']);
    });
    Route::prefix('/bookings')->group(function () {
        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::get('/get-bookings', [BookingsController::class, 'getBookings']);
            Route::post('/create-booking', [BookingsController::class, 'createBooking']);
            Route::put('/update-booking', [BookingsController::class, 'updateBooking']);
     });
    });
    Route::prefix('/loyalty')->group(function (){
        Route::get('/get-tiers', [LoyaltyTierController::class, 'fetchTiers']);
        // Route::group(['middleware' => 'auth:sanctum'], function () {
        //     Route::get('/get-tiers', [LoyaltyTierController::class, 'fetchTiers']);
        // });//temperory
    });
});

?>


