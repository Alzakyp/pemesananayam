<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Artisan;

// Route::get('/', function () {
//     return view('layouts.app');
// });

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'loginProses'])->name('login.proses');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'role:admin'])->name('dashboard'); 



// Route untuk produk
Route::prefix('produk')->group(function () {
    Route::get('/', [ProdukController::class, 'index'])->name('produk.index'); // Menampilkan daftar produk
    Route::get('/create', [ProdukController::class, 'create'])->name('produk.create'); // Form tambah produk
    Route::post('/', [ProdukController::class, 'store'])->name('produk.store'); // Menyimpan produk baru
    Route::get('/{id}', [ProdukController::class, 'show'])->name('produk.show'); // Menampilkan detail produk
    Route::get('/{id}/edit', [ProdukController::class, 'edit'])->name('produk.edit'); // Form edit produk
    Route::put('/{id}', [ProdukController::class, 'update'])->name('produk.update'); // Mengupdate produk
    Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy'); // Menghapus produk
});

Route::resource('pesanan', PesananController::class);


// Route untuk Pembayaran - MONITORING ONLY, tidak ada create/reject manual
Route::get('/pembayaran', [App\Http\Controllers\PembayaranController::class, 'index'])->name('pembayaran.index');
Route::get('/pembayaran/{id}', [App\Http\Controllers\PembayaranController::class, 'show'])->name('pembayaran.show');
Route::get('/pembayaran/{id}/edit', [App\Http\Controllers\PembayaranController::class, 'edit'])->name('pembayaran.edit');
Route::put('/pembayaran/{id}', [App\Http\Controllers\PembayaranController::class, 'update'])->name('pembayaran.update');

Route::resource('user', UserController::class);


Route::post('/pesanan/{id}/update-status', [PesananController::class, 'updateStatus'])->name('pesanan.updateStatus');

// Add route for manually processing daily orders (optional, for admin use)
Route::get('/process-daily-orders', function () {
    Artisan::call('orders:process-daily');
    return back()->with('success', 'Pesanan untuk tanggal hari ini telah diproses');
})->middleware(['auth', 'role:admin'])->name('process-daily-orders');
