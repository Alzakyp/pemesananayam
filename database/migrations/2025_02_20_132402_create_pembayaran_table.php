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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pesanan')->constrained('pesanan')->onDelete('cascade');
            // Metode pembayaran
            $table->enum('metode', ['Tunai', 'Midtrans'])->default('Tunai');

            // Bukti transfer untuk pembayaran manual
            $table->string('bukti_transfer')->nullable();

            // Kolom-kolom untuk Midtrans
            $table->string('midtrans_transaction_id')->nullable();
            // Status transaksi dari Midtrans: pending, settlement, deny, cancel, expire, dll
            $table->string('midtrans_status')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->string('midtrans_payment_url')->nullable();

            // Status pemrosesan internal aplikasi
            $table->enum('status_pemrosesan', ['Menunggu Pembayaran', 'Diproses', 'Selesai', 'Ditolak'])->default('Menunggu Pembayaran');

            $table->timestamp('tanggal_pembayaran')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
