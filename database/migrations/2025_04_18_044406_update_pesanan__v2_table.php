<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Karena tidak bisa mengubah enum langsung, kita perlu mengubah tipe kolom
        Schema::table('pesanan', function (Blueprint $table) {
            // Drop kolom status yang lama
            $table->dropColumn('status');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            // Buat kolom status yang baru dengan enum yang diperbarui
            $table->enum('status', ['Mempersiapkan', 'Proses pengantaran', 'Selesai', 'Dibatalkan'])
                ->default('Mempersiapkan')
                ->after('lokasi_maps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->enum('status', ['Menunggu Konfirmasi', 'Diproses', 'Siap Diambil', 'Dikirim', 'Selesai', 'Dibatalkan'])
                ->default('Menunggu Konfirmasi')
                ->after('lokasi_maps');
        });
    }
};
