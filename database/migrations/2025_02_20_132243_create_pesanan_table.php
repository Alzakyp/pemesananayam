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
            $table->foreignId('id_pelanggan')->constrained('users')->onDelete('cascade');
            $table->text('alamat_pengiriman');
            $table->foreignId('id_produk')->constrained('produk')->onDelete('cascade');
            $table->decimal('total_bayar', 10, 2);
            $table->enum('metode_pembayaran', ['Transfer', 'Tunai']);
            $table->enum('metode_pengiriman', ['Delivery', 'Pick Up']);
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
