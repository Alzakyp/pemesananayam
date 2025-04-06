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
            $table->string('bukti_transfer')->nullable(); // Hanya diisi jika transfer
            $table->enum('status', ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'])->default('Menunggu Verifikasi');
            $table->timestamp('tanggal_pembayaran')->useCurrent();
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
