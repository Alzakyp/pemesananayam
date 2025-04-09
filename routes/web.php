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




// Update your test-midtrans route

use App\Services\MidtransService;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Support\Facades\DB;

Route::get('/test-midtrans', function () {
    try {
        // Cek apakah ada pesanan
        $pesanan = Pesanan::first();

        // Jika tidak ada pesanan, buat dummy pesanan untuk test
        if (!$pesanan) {
            DB::beginTransaction();

            // Cek apakah ada produk
            $produk = Produk::first();
            if (!$produk) {
                // Buat produk dummy jika tidak ada
                $produk = Produk::create([
                    'nama_produk' => 'Ayam Test',
                    'harga' => 50000,
                    'stok' => 10,
                    'satuan' => 'Kg',
                ]);
            }

            // Cek apakah ada user
            $user = User::where('role', 'pelanggan')->first();
            if (!$user) {
                // Buat user dummy jika tidak ada
                $user = User::create([
                    'nama' => 'Pelanggan Test',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'no_hp' => '081234567890',
                    'role' => 'pelanggan'
                ]);
            }

            // Buat pesanan dummy
            $pesanan = Pesanan::create([
                'id_pelanggan' => $user->id,
                'id_produk' => $produk->id,
                'alamat_pengiriman' => 'Alamat Test',
                'total_bayar' => 100000,
                'metode_pembayaran' => 'Midtrans',
                'metode_pengiriman' => 'Delivery',
                'status' => 'Menunggu Konfirmasi',
                'tanggal_pemesanan' => now(),
            ]);

            DB::commit();

            $message = 'Dummy order berhasil dibuat untuk testing';
        } else {
            $message = 'Menggunakan order yang sudah ada';
        }

        // Inisialisasi MidtransService
        $midtransService = new MidtransService();

        // Buat transaksi test
        $result = $midtransService->createTransaction($pesanan);

        // PERBAIKAN: Jika result berhasil dan punya redirect URL, redirect ke URL tersebut
        if ($result['success'] && isset($result['redirect_url'])) {
            // Tambahkan header untuk tampilan debug
            header('Content-Type: text/html');
            echo '<h1>Redirecting to Midtrans...</h1>';
            echo '<p>If not redirected automatically, <a href="'.$result['redirect_url'].'">click here</a>.</p>';
            echo '<script>window.location.href="'.$result['redirect_url'].'";</script>';
            exit;
        }

        // Jika tidak ada redirect URL atau kita ingin lihat response
        return response()->json([
            'success' => true,
            'message' => 'Koneksi Midtrans berhasil. ' . $message,
            'data' => $result,
            'midtrans_config' => [
                'server_key' => substr(env('MIDTRANS_SERVER_KEY'), 0, 10) . '...',
                'client_key' => env('MIDTRANS_CLIENT_KEY'),
                'is_production' => env('MIDTRANS_IS_PRODUCTION', false) ? 'Production' : 'Sandbox',
            ],
            'pesanan' => [
                'id' => $pesanan->id,
                'total' => $pesanan->total_bayar
            ],
            'redirect_url' => $result['redirect_url'] ?? null
        ]);
    } catch (\Exception $e) {
        if (isset($pesanan) && isset($message) && $message === 'Dummy order berhasil dibuat untuk testing') {
            DB::rollBack();
        }

        return response()->json([
            'success' => false,
            'message' => 'Error saat menghubungi Midtrans: ' . $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'midtrans_config' => [
                'server_key_set' => !empty(env('MIDTRANS_SERVER_KEY')),
                'client_key_set' => !empty(env('MIDTRANS_CLIENT_KEY')),
                'is_production' => env('MIDTRANS_IS_PRODUCTION', false) ? 'Production' : 'Sandbox',
            ]
        ], 500);
    }
})->name('test-midtrans');
