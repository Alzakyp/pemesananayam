<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            // Ubah id_pelanggan menjadi nullable untuk guest checkout
            $table->foreignId('id_pelanggan')->nullable()->constrained('users')->onDelete('cascade');
            // Tambah kolom nama dan no_hp untuk guest checkout
            $table->string('nama')->nullable(); // Untuk guest checkout
            $table->string('no_hp', 15)->nullable(); // Untuk guest checkout
            $table->text('alamat_pengiriman');
            $table->foreignId('id_produk')->constrained('produk')->onDelete('cascade');
            $table->integer('jumlah')->default(1); // Jumlah produk yang dipesan
            $table->decimal('berat', 8, 2)->nullable(); // Berat dalam kg jika diperlukan
            $table->decimal('total_bayar', 10, 2);
            // Ubah metode pembayaran dari 'Transfer' ke 'Midtrans'
            $table->enum('metode_pembayaran', ['Midtrans', 'Tunai']);
            $table->enum('metode_pengiriman', ['Delivery', 'Pick Up']);
            // Tambah kolom lokasi_maps untuk koordinat lokasi pengiriman
            $table->text('lokasi_maps')->nullable();
            $table->enum('status', ['Menunggu Konfirmasi', 'Diproses', 'Siap Diambil', 'Dikirim', 'Selesai', 'Dibatalkan'])->default('Menunggu Konfirmasi');
            $table->timestamp('tanggal_pemesanan')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
