<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Pembayaran;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        // Konfigurasi Midtrans
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Buat transaksi baru di Midtrans
     */
    public function createTransaction(Pesanan $pesanan)
    {
        // Buat ID transaksi unik
        $orderId = 'ORDER-' . $pesanan->id . '-' . time();

        // Data transaksi untuk Midtrans
        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $pesanan->total_bayar,
        ];

        // Detail pelanggan
        $customerDetails = [
            'first_name' => $pesanan->pelanggan->nama ?? ($pesanan->nama ?? 'Customer'),
            'phone' => $pesanan->pelanggan->no_hp ?? ($pesanan->no_hp ?? '08123456789'),
            'billing_address' => [
                'address' => $pesanan->alamat_pengiriman ?? 'No address provided',
            ]
        ];

        // Detail item yang dibeli
        $items = [];

        // Jika menggunakan detail_pesanan
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $detail) {
                $items[] = [
                    'id' => $detail->produk->id,
                    'price' => (int) $detail->harga,
                    'quantity' => $detail->jumlah,
                    'name' => $detail->produk->nama_produk . ($detail->berat ? " ({$detail->berat}kg)" : ""),
                ];
            }
        }
        // Jika langsung dari tabel pesanan dan memiliki relasi dengan produk
        else if ($pesanan->produk) {
            $items[] = [
                'id' => $pesanan->produk->id,
                'price' => (int) $pesanan->produk->harga,
                'quantity' => $pesanan->jumlah ?? 1,
                'name' => $pesanan->produk->nama_produk,
            ];
        }
        // Fallback jika tidak ada detail produk
        else {
            $items[] = [
                'id' => 'product-1',
                'price' => (int) $pesanan->total_bayar,
                'quantity' => 1,
                'name' => 'Pesanan #' . $pesanan->id,
            ];
        }

        // Parameter untuk API Midtrans
        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $items,
        ];

        try {
            // Buat Snap Token dengan Midtrans
            $snapToken = Snap::getSnapToken($params);

            // PERBAIKAN: Membuat URL redirect yang benar
            $redirectUrl = 'https://app.' .
                (env('MIDTRANS_IS_PRODUCTION', false) ? '' : 'sandbox.') .
                'midtrans.com/snap/v2/vtweb/' . $snapToken;

            // Buat atau update record pembayaran
            $pembayaran = Pembayaran::updateOrCreate(
                ['id_pesanan' => $pesanan->id],
                [
                    'metode' => 'Midtrans',
                    'midtrans_transaction_id' => $orderId,
                    'midtrans_payment_url' => $redirectUrl,
                    'status_pemrosesan' => 'Menunggu Pembayaran'
                ]
            );

            return [
                'success' => true,
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
