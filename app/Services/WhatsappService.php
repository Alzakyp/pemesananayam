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
     * Kirim notifikasi status pesanan
     * @param Pesanan $pesanan
     * @param string $status
     * @return bool
     */
    public function sendOrderStatusNotification(Pesanan $pesanan, string $status)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        switch ($status) {
            case 'Siap diambil':
                return $this->sendOrderReadyNotification($pesanan);
            case 'Proses pengantaran':
                return $this->sendDeliveryNotification($pesanan);
            default:
                return false;
        }
    }

    /**
     * Kirim notifikasi pembayaran berhasil
     * @param Pesanan $pesanan
     * @return bool
     */
    public function sendPaymentSuccessNotification(Pesanan $pesanan)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        $message = "Halo {$customerName},\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky.\n\n";
        $message .= "Pembayaran Anda untuk pesanan #{$pesanan->id} telah kami terima.\n\n";

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " ({$detail->jumlah} " . ($detail->berat ? "x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "- " . $pesanan->produk->nama_produk;
            $message .= " ({$pesanan->jumlah} " . ($pesanan->berat ? "x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        // Informasi pengiriman
        if ($pesanan->metode_pengiriman == 'Delivery') {
            $message .= "Pesanan akan dikirim ke alamat:\n{$pesanan->alamat_pengiriman}\n\n";
        } else {
            $message .= "Pesanan dapat diambil di toko kami.\n\n";
        }

        $message .= "Terima kasih telah mempercayakan kebutuhan ayam Anda kepada kami!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Kirim notifikasi pesanan siap diambil
     * @param Pesanan $pesanan
     * @return bool
     */
    public function sendOrderReadyNotification(Pesanan $pesanan)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        $message = "Halo {$customerName},\n\n";
        $message .= "Pesanan Anda #{$pesanan->id} di UD. Ayam Potong Rizky telah SIAP DIAMBIL.\n\n";

        // Tambahkan informasi status pembayaran berbeda berdasarkan metode
        if ($pesanan->metode_pembayaran == 'Midtrans') {
            $message .= "Status pembayaran: LUNAS\n";
            $message .= "Terima kasih atas pembayaran Anda.\n\n";
        } else {
            $message .= "Status pembayaran: TUNAI\n";
            $message .= "Silakan lakukan pembayaran saat pengambilan pesanan sebesar Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";
        }

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " ({$detail->jumlah} " . ($detail->berat ? "x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "- " . $pesanan->produk->nama_produk;
            $message .= " ({$pesanan->jumlah} " . ($pesanan->berat ? "x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Pesanan Anda telah siap dan dapat diambil di toko kami dengan menunjukkan pesan ini.\n";
        $message .= "Alamat: Jl. Raya Utama No. 123, Kota Anda\n\n";
        $message .= "Terima kasih telah berbelanja di UD. Ayam Potong Rizky!";

        return $this->sendMessage($phoneNumber, $message);
    }

    /**
     * Kirim notifikasi pesanan dalam proses pengantaran
     * @param Pesanan $pesanan
     * @return bool
     */
    public function sendDeliveryNotification(Pesanan $pesanan)
    {
        $phoneNumber = $this->formatPhoneNumber($pesanan->pelanggan->no_hp ?? $pesanan->no_hp);
        $customerName = $pesanan->pelanggan->nama ?? $pesanan->nama;

        $message = "Halo {$customerName},\n\n";
        $message .= "Pesanan Anda #{$pesanan->id} di UD. Ayam Potong Rizky sedang DALAM PERJALANAN.\n\n";

        // Tambahkan informasi status pembayaran berbeda berdasarkan metode
        if ($pesanan->metode_pembayaran == 'Midtrans') {
            $message .= "Status pembayaran: LUNAS\n";
            $message .= "Terima kasih atas pembayaran Anda.\n\n";
        } else {
            $message .= "Status pembayaran: TUNAI\n";
            $message .= "Silakan siapkan pembayaran tunai sebesar Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";
        }

        // Detail pesanan
        $message .= "DETAIL PESANAN:\n";
        if ($pesanan->detailPesanan && $pesanan->detailPesanan->count() > 0) {
            foreach ($pesanan->detailPesanan as $index => $detail) {
                $message .= ($index + 1) . ". " . $detail->produk->nama_produk;
                $message .= " ({$detail->jumlah} " . ($detail->berat ? "x {$detail->berat}kg" : "") . ")";
                $message .= " - Rp " . number_format($detail->subtotal, 0, ',', '.') . "\n";
            }
        } else {
            $message .= "- " . $pesanan->produk->nama_produk;
            $message .= " ({$pesanan->jumlah} " . ($pesanan->berat ? "x {$pesanan->berat}kg" : "") . ")";
            $message .= " - Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n";
        }

        $message .= "\nTotal: Rp " . number_format($pesanan->total_bayar, 0, ',', '.') . "\n\n";

        $message .= "Pesanan akan dikirim ke alamat.\n\n";
        $message .= "Alamat pengiriman: {$pesanan->alamat_pengiriman}\n\n";
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
