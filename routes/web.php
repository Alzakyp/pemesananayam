<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;

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
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'role:admin'])->name('dashboard');
// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');




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
//Route untuk pesanan

// Route::middleware(['auth'])->group(function () {
//     Route::resource('pesanan', PesananController::class);
// });
Route::resource('pesanan', PesananController::class);

Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
Route::get('/pembayaran/{id}', [PembayaranController::class, 'show'])->name('pembayaran.show');
Route::post('/pembayaran/verifikasi/{id}', [PembayaranController::class, 'verifikasi'])->name('pembayaran.verifikasi');
Route::delete('/pembayaran/{id}', [PembayaranController::class, 'destroy'])->name('pembayaran.destroy');


Route::resource('user', UserController::class);
// Route::get('/users', [UserController::class, 'index'])->name('user.index');
// Route::get('/users/create', [UserController::class, 'create'])->name('user.create');
// Route::post('/users', [UserController::class, 'store'])->name('user.store');
// Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('user.edit');
// Route::put('/users/{id}', [UserController::class, 'update'])->name('user.update');
// Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.destroy');

//Route untuk login
// Route::get('admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
// Route::post('admin/login', [AdminAuthController::class, 'login']);

// Route::get('produk.index', function () {
    // return view('produk.index'); // Buat view untuk dashboard admin
// })->name('produk.index')->middleware('auth');


// Route::prefix('admin')->group(function () {
//     Route::resource('users', UserController::class);
// });


