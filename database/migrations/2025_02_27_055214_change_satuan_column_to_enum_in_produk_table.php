<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeSatuanColumnToEnumInProdukTable extends Migration
{
    public function up()
    {
        Schema::table('produk', function (Blueprint $table) {
            // Hapus kolom 'satuan' jika ada dan tambahkan sebagai ENUM
            $table->dropColumn('satuan');
            $table->enum('satuan', ['Kg', 'pcs', 'ons', 'ekor'])->after('stok');
        });
    }

    public function down()
    {
        Schema::table('produk', function (Blueprint $table) {
            $table->dropColumn('satuan');
            $table->integer('satuan')->after('stok'); // Kembalikan ke integer jika rollback
        });
    }
}

