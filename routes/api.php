<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ApiAuthController;
use App\Http\Controllers\API\ApiHistoryController;
use App\Http\Controllers\API\ApiProdukController;
use App\Http\Controllers\API\ApiPesananController;
use App\Http\Controllers\API\MidtransCallbackController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route autentikasi API
Route::prefix('auth')->group(function () {
    Route::post('/register', [ApiAuthController::class, 'register']);
    Route::post('/login', [ApiAuthController::class, 'login']);

    // Routes yang membutuhkan autentikasi
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
        Route::get('/profile', [ApiAuthController::class, 'profile']);
        Route::post('/profile/update', [ApiAuthController::class, 'updateProfile']);


        // Order history routes
        Route::get('/history', [ApiHistoryController::class, 'index']);
        Route::get('/history/{id}', [ApiHistoryController::class, 'show']);
    });
});

// Routes untuk produk (akses publik, tidak perlu login)
Route::prefix('produk')->group(function () {
    Route::get('/', [ApiProdukController::class, 'index']);
    Route::get('/kategori/{kategori}', [ApiProdukController::class, 'getByKategori']);
    Route::get('/search', [ApiProdukController::class, 'search']);
    Route::get('/{id}', [ApiProdukController::class, 'show']);
});

// Routes untuk pesanan (perlu autentikasi)
Route::prefix('pesanan')->group(function () {
    // Routes yang memerlukan autentikasi
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [ApiPesananController::class, 'index']);
        Route::post('/', [ApiPesananController::class, 'store']);
        Route::get('/{id}', [ApiPesananController::class, 'show']);
        Route::post('/{id}/cancel', [ApiPesananController::class, 'cancelOrder']);
        Route::get('/{id}/status', [ApiPesananController::class, 'checkStatus']);
    });

    // Guest checkout (tidak perlu login)
    Route::post('/guest', [ApiPesananController::class, 'guestStore']);
    Route::get('/guest/{id}/status', [ApiPesananController::class, 'guestCheckStatus']);
});

// Route untuk callback Midtrans
Route::post('/midtrans-callback', [MidtransCallbackController::class, 'handle']);
Route::post('/midtrans/token', [MidtransCallbackController::class, 'getToken']);

Route::post('/track-order', [ApiHistoryController::class, 'trackGuestOrder']);
