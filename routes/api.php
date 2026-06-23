<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\GeocodingController;
use App\Http\Controllers\Api\MidtransPaymentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Middleware\EnsureAccessToken;
use Illuminate\Support\Facades\Route;

Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['throttle:api-mobile-public'])
    ->name('verification.verify');
Route::post('/payments/midtrans/notification', [MidtransPaymentController::class, 'notification'])
    ->middleware('throttle:api-midtrans-webhook');

Route::middleware(['api.key', 'throttle:api-mobile-public'])->group(function () {
    Route::middleware('throttle:api-auth-sensitive')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/token/refresh', [AuthController::class, 'refreshToken']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/forgot-password/resend', [AuthController::class, 'resendForgotPasswordOtp']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        Route::post('/email/verification-notification', [AuthController::class, 'resendVerification']);
    });

    Route::get('/store', [StoreController::class, 'show']);
    Route::get('/store/coverage', [StoreController::class, 'coverage']);
    Route::get('/geocode/search', [GeocodingController::class, 'search']);
    Route::get('/geocode/reverse', [GeocodingController::class, 'reverse']);
    Route::get('/categories', [ProductCategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/search/products', [ProductController::class, 'search']);

    Route::middleware([
        'auth:sanctum',
        EnsureAccessToken::class,
        'throttle:api-mobile-auth',
    ])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword'])
            ->middleware('throttle:api-auth-sensitive');

        Route::get('/me', [ProfileController::class, 'show']);
        Route::patch('/me', [ProfileController::class, 'update']);
        Route::post('/me/avatar', [ProfileController::class, 'updateAvatar']);
        Route::patch('/me/fcm-token', [ProfileController::class, 'updateFcmToken']);

        Route::get('/addresses', [UserAddressController::class, 'index']);
        Route::post('/addresses', [UserAddressController::class, 'store']);
        Route::patch('/addresses/{id}/default', [UserAddressController::class, 'setDefault']);
        Route::get('/addresses/{id}', [UserAddressController::class, 'show']);
        Route::patch('/addresses/{id}', [UserAddressController::class, 'update']);
        Route::delete('/addresses/{id}', [UserAddressController::class, 'destroy']);

        Route::get('/cart', [CartController::class, 'show']);
        Route::post('/cart/items', [CartController::class, 'store'])->middleware('idempotency');
        Route::patch('/cart/items/{id}', [CartController::class, 'update'])->middleware('idempotency');
        Route::delete('/cart/items/{id}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        Route::post('/checkout/preview', [CheckoutController::class, 'preview']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store'])->middleware('idempotency');
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel'])->middleware('idempotency');
        Route::post('/orders/{id}/payment', [MidtransPaymentController::class, 'create'])->middleware('idempotency');
        Route::get('/orders/{id}/payment', [MidtransPaymentController::class, 'status']);
    });
});
