<?php

namespace App\Services;

use App\Models\Pesanan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $apiUrl;
    protected $token;

    public function __construct()
    {
        // Ganti dengan URL dan token API Fonnte
        $this->apiUrl = env('WHATSAPP_API_URL', 'https://api.fonnte.com/send');
        $this->token = env('WHATSAPP_API_TOKEN');
    }

    /**
     * Notifikasi terpadu untuk semua jenis pesanan - mencegah duplikasi pesan
     *
     * @param Pesanan $pesanan
     * @return bool
     */
    public function sendUnifiedNotification(Pesanan $pesanan)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        // Format pesan untuk delivery atau pickup sesuai permintaan
        if ($pesanan->metode_pengiriman == 'Delivery') {
            return $this->sendDeliveryConfirmation($pesanan, $phoneNumber, $customerName);
        } else {
            return $this->sendPickupConfirmation($pesanan, $phoneNumber, $customerName);
        }
    }

    /**
     * Notifikasi khusus untuk pesanan tunai baru
     *
     * @param Pesanan $pesanan
     * @return bool
     */
    public function sendCashOrderNotification(Pesanan $pesanan)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        if ($pesanan->metode_pengiriman == 'Delivery') {
            return $this->sendCashDeliveryNotification($pesanan, $phoneNumber, $customerName);
        } else {
            return $this->sendCashPickupNotification($pesanan, $phoneNumber, $customerName);
        }
    }

    /**
     * Kirim konfirmasi pengiriman dengan format yang diminta
     */
    private function sendDeliveryConfirmation(Pesanan $pesanan, $phoneNumber, $customerName)
    {
        // Menggunakan format persis seperti yang diminta
        $message = "Halo {$customerName},\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky.\n\n";
        $message .= "Pembayaran Anda untuk pesanan #{$pesanan->id} telah kami terima.\n\n";

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " (" . $detail->jumlah . ($detail->berat ? " x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->harga * $detail->jumlah, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "1. " . $pesanan->produk->nama_produk;
            $message .= " (" . $pesanan->jumlah . ($pesanan->berat ? " x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Pesanan akan dikirim ke alamat:\n";
        $message .= "{$pesanan->alamat_pengiriman}\n\n";

        $message .= "Terima kasih telah mempercayakan kebutuhan ayam Anda kepada kami!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Kirim konfirmasi pesanan siap diambil
     */
    private function sendPickupConfirmation(Pesanan $pesanan, $phoneNumber, $customerName)
    {
        $message = "Halo {$customerName},\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky.\n\n";
        $message .= "Pembayaran Anda untuk pesanan #{$pesanan->id} telah kami terima.\n\n";

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " (" . $detail->jumlah . ($detail->berat ? " x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->harga * $detail->jumlah, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "1. " . $pesanan->produk->nama_produk;
            $message .= " (" . $pesanan->jumlah . ($pesanan->berat ? " x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Pesanan Anda telah SIAP DIAMBIL di toko kami.\n";
        $message .= "Alamat: Jl. Raya Utama No. 123, Kota Anda\n\n";

        $message .= "Terima kasih telah mempercayakan kebutuhan ayam Anda kepada kami!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Notifikasi untuk pesanan tunai dengan pengiriman
     */
    private function sendCashDeliveryNotification(Pesanan $pesanan, $phoneNumber, $customerName)
    {
        $message = "Halo {$customerName},\n\n";
        $message .= "Terima kasih telah memesan di UD. Ayam Potong Rizky.\n\n";
        $message .= "Pesanan Anda #{$pesanan->id} sedang DALAM PERJALANAN.\n\n";
        $message .= "Status pembayaran: TUNAI\n";
        $message .= "Silakan siapkan pembayaran tunai sebesar Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " (" . $detail->jumlah . ($detail->berat ? " x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->harga * $detail->jumlah, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "1. " . $pesanan->produk->nama_produk;
            $message .= " (" . $pesanan->jumlah . ($pesanan->berat ? " x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Alamat pengiriman: {$pesanan->alamat_pengiriman}\n\n";
        $message .= "Kurir kami sedang menuju lokasi Anda.\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Notifikasi untuk pesanan tunai siap diambil
     */
    private function sendCashPickupNotification(Pesanan $pesanan, $phoneNumber, $customerName)
    {
        $message = "Halo {$customerName},\n\n";
        $message .= "Terima kasih telah memesan di UD. Ayam Potong Rizky.\n\n";
        $message .= "Pesanan Anda #{$pesanan->id} telah SIAP DIAMBIL.\n\n";
        $message .= "Status pembayaran: TUNAI\n";
        $message .= "Silakan lakukan pembayaran saat pengambilan pesanan sebesar Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " (" . $detail->jumlah . ($detail->berat ? " x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->harga * $detail->jumlah, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "1. " . $pesanan->produk->nama_produk;
            $message .= " (" . $pesanan->jumlah . ($pesanan->berat ? " x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Pesanan Anda dapat diambil di toko kami dengan menunjukkan pesan ini.\n";
        $message .= "Alamat: Jl. Raya Utama No. 123, Kota Anda\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Format nomor telepon untuk WhatsApp API
     */
    private function formatPhoneNumber($phone)
    {
        // Bersihkan format
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Ubah awalan 0 menjadi 62 (kode Indonesia)
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    /**
     * Kirim pesan WhatsApp menggunakan API Fonnte
     */
    private function sendMessage($phone, $message)
    {
        if (empty($this->apiUrl) || empty($this->token)) {
            Log::warning('WhatsApp API URL atau token belum dikonfigurasi');
            return false;
        }

        try {
            // Implementasi untuk Fonnte API
            $response = Http::withHeaders([
                'Authorization' => $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, [
                'target' => $phone,
                'message' => $message,
                'countryCode' => '62', // Indonesia
            ]);

            if (!$response->successful()) {
                Log::error('WhatsApp Fonnte API Error: ' . $response->body());
                return false;
            }

            Log::info("WhatsApp notification sent to {$phone} successfully");
            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error: ' . $e->getMessage());
            return false;
        }
    }
}
