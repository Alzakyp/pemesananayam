<?php

namespace App\Services;

use App\Models\User;
use App\Models\Pesanan;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized = config('services.midtrans.sanitized', true);
        Config::$is3ds = config('services.midtrans.enable_3ds', true);
    }

    /**
     * Buat transaksi baru di Midtrans
     * Pesanan harus sudah dibuat di database sebelum memanggil method ini
     */
    public function createTransaction(Pesanan $pesanan)
    {
        // Pastikan pesanan sudah ada di database
        if (!$pesanan->exists) {
            Log::error('Attempted to create Midtrans transaction for non-existent order', [
                'pesanan_data' => $pesanan->toArray()
            ]);
            return [
                'success' => false,
                'message' => 'Error: Pesanan tidak ditemukan di database'
            ];
        }

        // Generate order ID yang akan digunakan di Midtrans
        if ($pesanan->id_pelanggan) {
            // Format untuk registered user: SIAYAM-USER-{date}-{time}-{userId}
            $dateTime = now();
            $orderId = 'SIAYAM-USER-' .
                $dateTime->format('Ymd') . '-' .
                $dateTime->format('His') . '-' .
                $pesanan->id_pelanggan;
        } else {
            // Format untuk guest: SIAYAM-GUEST-{date}-{time}-G
            $dateTime = now();
            $orderId = 'SIAYAM-GUEST-' .
                $dateTime->format('Ymd') . '-' .
                $dateTime->format('His') . '-G';
        }

        // Simpan order_id ke pesanan agar dapat digunakan oleh webhook
        $pesanan->midtrans_order_id = $orderId;
        $pesanan->save();

        $transactionDetails = [
            'order_id' => $orderId,
            'gross_amount' => (int) $pesanan->total_bayar,
        ];

        $customerDetails = [
            'first_name' => $pesanan->pelanggan->nama ?? ($pesanan->nama ?? 'Customer'),
            'phone' => $pesanan->pelanggan->no_hp ?? ($pesanan->no_hp ?? '08123456789'),
            'billing_address' => [
                'address' => $pesanan->alamat_pengiriman ?? 'No address provided',
            ]
        ];

        $items = [];
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $detail) {
                $items[] = [
                    'id' => $detail->produk->id,
                    'price' => (int) $detail->harga,
                    'quantity' => $detail->jumlah,
                    'name' => $detail->produk->nama_produk . ($detail->berat ? " ({$detail->berat}kg)" : ""),
                ];
            }
        } else {
            // Fallback jika tidak ada detail pesanan
            $items[] = [
                'id' => 'product-1',
                'price' => (int) $pesanan->total_bayar,
                'quantity' => 1,
                'name' => 'Pesanan #' . $pesanan->id,
            ];
        }

        $params = [
            'transaction_details' => $transactionDetails,
            'customer_details' => $customerDetails,
            'item_details' => $items,
        ];

        try {
            Log::info('Creating Midtrans transaction', [
                'pesanan_id' => $pesanan->id,
                'order_id' => $orderId,
                'amount' => $pesanan->total_bayar
            ]);

            $snapToken = Snap::getSnapToken($params);

            $redirectUrl = 'https://app.' .
                (env('MIDTRANS_IS_PRODUCTION', false) ? '' : 'sandbox.') .
                'midtrans.com/snap/v2/vtweb/' . $snapToken;

            // Update atau buat pembayaran
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
            Log::error('Error creating Midtrans transaction', [
                'pesanan_id' => $pesanan->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function processNotification(array $notification)
    {
        Log::info('Processing Midtrans notification', $notification);

        $orderId = $notification['order_id'] ?? '';
        $transactionStatus = $notification['transaction_status'] ?? '';
        $fraudStatus = $notification['fraud_status'] ?? null;
        $paymentType = $notification['payment_type'] ?? '';
        $amount = $notification['gross_amount'] ?? 0;

        if (strpos($orderId, 'payment_notif_test') !== false) {
            Log::info("Test notification processed successfully");
            return true;
        }

        try {
            $pembayaran = null;
            $pesanan = null;

            // Cari pembayaran berdasarkan midtrans_transaction_id
            $pembayaran = Pembayaran::where('midtrans_transaction_id', $orderId)->first();

            // Jika pembayaran tidak ditemukan, cari pesanan berdasarkan midtrans_order_id
            if (!$pembayaran) {
                $pesanan = Pesanan::where('midtrans_order_id', $orderId)->first();

                // TAMBAHKAN KODE INI: Jika order_id adalah angka sederhana, coba cari berdasarkan ID pesanan
                if (!$pesanan && is_numeric($orderId)) {
                    $pesanan = Pesanan::find($orderId);

                    if ($pesanan) {
                        Log::info("Pesanan ditemukan berdasarkan ID langsung: {$orderId}");
                    }
                }

                if ($pesanan) {
                    // Pesanan ditemukan, buat pembayaran
                    $pembayaran = Pembayaran::updateOrCreate(
                        ['id_pesanan' => $pesanan->id],
                        [
                            'metode' => 'Midtrans',
                            'midtrans_transaction_id' => $orderId,
                            'status_pemrosesan' => 'Menunggu Pembayaran'
                        ]
                    );

                    Log::info("Created payment record for order with ID: {$pesanan->id}");
                } else {
                    // Log error jika pesanan tidak ditemukan
                    Log::error("Pesanan tidak ditemukan untuk order_id: {$orderId}");
                    return false;
                }
            } else {
                // Pembayaran ditemukan, ambil pesanan terkait
                $pesanan = $pembayaran->pesanan;

                if (!$pesanan) {
                    Log::error("Pembayaran ditemukan tetapi pesanan terkait tidak ada untuk order_id: {$orderId}");
                    return false;
                }
            }

            // Update status pembayaran berdasarkan notifikasi
            $this->updatePaymentStatus($pembayaran, $transactionStatus, $fraudStatus, $paymentType, $notification);
            return true;
        } catch (\Exception $e) {
            Log::error('Error processing Midtrans notification: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updatePaymentStatus($pembayaran, $transactionStatus, $fraudStatus, $paymentType, $notification)
    {
        Log::info("Updating payment status for ID {$pembayaran->id} to {$transactionStatus}");

        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $pembayaran->status_pemrosesan = 'Diproses';
                $pembayaran->tanggal_pembayaran = now();

                $pesanan = $pembayaran->pesanan;
                if ($pesanan && $pesanan->status == 'Mempersiapkan') {
                    $oldStatus = $pesanan->status;

                    if ($pesanan->metode_pengiriman == 'Delivery') {
                        $pesanan->status = 'Proses pengantaran';
                    } else {
                        $pesanan->status = 'Siap Diambil';
                    }
                    $pesanan->tanggal_pembayaran = now();

                    // Simpan detail pembayaran ke pesanan
                    $paymentDetails = [
                        'payment_type' => $notification['payment_type'] ?? null,
                        'transaction_time' => $notification['transaction_time'] ?? null,
                        'transaction_id' => $notification['transaction_id'] ?? null,
                        'settlement_time' => $notification['settlement_time'] ?? null,
                        'amount' => $notification['gross_amount'] ?? null,
                    ];

                    if (isset($notification['va_numbers']) && !empty($notification['va_numbers'])) {
                        $paymentDetails['bank'] = $notification['va_numbers'][0]['bank'] ?? null;
                        $paymentDetails['va_number'] = $notification['va_numbers'][0]['va_number'] ?? null;
                    }

                    $pesanan->payment_details = json_encode($paymentDetails);
                    $pesanan->save();

                    Log::info("Order {$pesanan->id} updated to status: {$pesanan->status}");

                    // MODIFIKASI: Hanya kirim satu notifikasi dengan format sesuai
                    try {
                        $whatsappService = new WhatsAppService();
                        // Kirim notifikasi tunggal dengan format yang sesuai
                        $whatsappService->sendUnifiedNotification($pesanan);
                        Log::info("Unified notification sent for order #{$pesanan->id}");
                    } catch (\Exception $e) {
                        Log::error('Failed to send WhatsApp notification: ' . $e->getMessage());
                    }
                }
                break;

            case 'pending':
                $pembayaran->status_pemrosesan = 'Menunggu Pembayaran';
                break;

            case 'deny':
            case 'expire':
            case 'cancel':
                $pembayaran->status_pemrosesan = 'Gagal';
                break;
        }

        $pembayaran->payment_details = json_encode($notification);
        $pembayaran->save();

        return $pembayaran;
    }

    public function getToken(array $data)
    {
        try {
            if (
                empty($data['order_id']) || empty($data['gross_amount']) ||
                empty($data['customer_details']) || empty($data['item_details'])
            ) {
                throw new \Exception('Missing required parameters');
            }

            $params = [
                'transaction_details' => [
                    'order_id' => $data['order_id'],
                    'gross_amount' => (int)$data['gross_amount'],
                ],
                'customer_details' => $data['customer_details'],
                'item_details' => $data['item_details']
            ];

            $snapToken = Snap::getSnapToken($params);

            $redirectUrl = 'https://app.' .
                (config('services.midtrans.is_production', false) ? '' : 'sandbox.') .
                'midtrans.com/snap/v2/vtweb/' . $snapToken;

            return [
                'success' => true,
                'data' => [
                    'snap_token' => $snapToken,
                    'redirect_url' => $redirectUrl,
                    'order_id' => $data['order_id']
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error generating Midtrans token: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function ensureGuestPaymentRecord(array $requestData, array $tokenData)
    {
        try {
            $orderId = $requestData['order_id'] ?? null;

            if (!$orderId || strpos($orderId, 'SIAYAM-GUEST-') !== 0) {
                return false;
            }

            $productId = $requestData['item_details'][0]['id'] ?? null;
            $productName = $requestData['item_details'][0]['name'] ?? 'Unknown Product';
            $quantity = $requestData['item_details'][0]['quantity'] ?? 1;
            $price = $requestData['item_details'][0]['price'] ?? $requestData['gross_amount'];
            $customerName = $requestData['customer_details']['first_name'] ?? 'Guest';
            $customerPhone = $requestData['customer_details']['phone'] ?? null;
            $address = $requestData['customer_details']['billing_address']['address'] ?? null;

            $pesanan = Pesanan::where('midtrans_order_id', $orderId)->first();

            if (!$pesanan) {
                // Buat pesanan untuk guest
                $pesanan = new Pesanan();
                $pesanan->id_produk = $productId;
                $pesanan->nama = $customerName;
                $pesanan->no_hp = $customerPhone;
                $pesanan->alamat_pengiriman = $address;
                $pesanan->jumlah = $quantity;
                $pesanan->total_bayar = $requestData['gross_amount'];
                $pesanan->metode_pembayaran = 'Midtrans';
                $pesanan->metode_pengiriman = 'Delivery';
                $pesanan->status = 'Mempersiapkan';
                $pesanan->tanggal_pemesanan = now();
                $pesanan->midtrans_order_id = $orderId;
                $pesanan->save();

                Log::info("Created order for mobile transaction: " . $pesanan->id);
            }

            $pembayaran = Pembayaran::updateOrCreate(
                ['midtrans_transaction_id' => $orderId],
                [
                    'id_pesanan' => $pesanan->id,
                    'metode' => 'Midtrans',
                    'status_pemrosesan' => 'Menunggu Pembayaran',
                    'midtrans_payment_url' => $tokenData['redirect_url'] ?? null
                ]
            );

            Log::info("Payment record created/updated for order: " . $orderId);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to ensure guest payment record: " . $e->getMessage(), [
                'request_data' => $requestData
            ]);
            return false;
        }
    }

    public function createPaymentRecord(Pesanan $pesanan, array $paymentResponse = null)
    {
        // Gunakan updateOrCreate untuk mencegah duplikasi
        return Pembayaran::updateOrCreate(
            ['id_pesanan' => $pesanan->id],
            [
                'metode' => $pesanan->metode_pembayaran,
                'status_pemrosesan' => 'Menunggu Pembayaran',
                'midtrans_transaction_id' => $paymentResponse['order_id'] ?? null,
                'midtrans_payment_url' => $paymentResponse['redirect_url'] ?? null
            ]
        );
    }
}
