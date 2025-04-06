<?php

use App\Http\Controllers\ApiPesananController;
use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\ApiProdukController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/produk', [ApiProdukController::class, 'index']);
Route::get('/produk/{id}', [ApiProdukController::class, 'show']);
Route::post('/produk', [ApiProdukController::class, 'store']);
Route::put('/produk/{id}', [ApiProdukController::class, 'update']);
Route::delete('/produk/{id}', [ApiProdukController::class, 'destroy']);

Route::post('/pesanan', [ApiPesananController::class, 'store']);


Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [ApiAuthController::class, 'logout']);
