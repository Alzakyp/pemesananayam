<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveProductColumnsFromPesanan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            // Hapus foreign key constraint
            $table->dropForeign(['id_produk']);

            // Hapus kolom-kolom
            $table->dropColumn(['id_produk', 'jumlah', 'berat']);

            // Tambah kolom stok_dikurangi jika diperlukan
            $table->boolean('stok_dikurangi')->default(false)->after('tanggal_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_produk')->nullable();
            $table->foreign('id_produk')->references('id')->on('produk');
            $table->integer('jumlah')->default(1);
            $table->decimal('berat', 8, 2)->nullable();
            $table->dropColumn('stok_dikurangi');
        });
    }
}
