<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan', 'payment_details')) {
                $table->json('payment_details')->nullable()->after('status');
            }

            if (!Schema::hasColumn('pesanan', 'tanggal_pembayaran')) {
                $table->timestamp('tanggal_pembayaran')->nullable()->after('tanggal_pemesanan');
            }

            if (!Schema::hasColumn('pesanan', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('status');
            }
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
            $table->dropColumn('payment_details');
            $table->dropColumn('tanggal_pembayaran');
            $table->dropColumn('midtrans_order_id');
        });
    }
};
