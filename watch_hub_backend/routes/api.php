<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => ['status' => 'ok', 'app' => 'WatchHub']);

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/home', [CatalogController::class, 'home']);
Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{product}', [CatalogController::class, 'show']);
Route::get('/brands', [CatalogController::class, 'brands']);
Route::get('/categories', [CatalogController::class, 'categories']);
Route::get('/faqs', [CatalogController::class, 'faqs']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AccountController::class, 'updateProfile']);

    Route::get('/cart', [AccountController::class, 'cart']);
    Route::post('/cart/{product}', [AccountController::class, 'addToCart']);
    Route::patch('/cart/items/{cart}', [AccountController::class, 'updateCart']);
    Route::delete('/cart/items/{cart}', [AccountController::class, 'removeCart']);

    Route::get('/wishlist', [AccountController::class, 'wishlist']);
    Route::post('/wishlist/{product}', [AccountController::class, 'toggleWishlist']);

    Route::get('/addresses', [AccountController::class, 'addresses']);
    Route::post('/addresses', [AccountController::class, 'storeAddress']);

    Route::get('/orders', [AccountController::class, 'orders']);
    Route::post('/orders', [AccountController::class, 'placeOrder']);

    Route::post('/products/{product}/reviews', [AccountController::class, 'storeReview']);

    Route::get('/support/tickets', [AccountController::class, 'supportTickets']);
    Route::post('/support/tickets', [AccountController::class, 'storeTicket']);

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
});
