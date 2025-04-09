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
        $this->apiUrl = env('WHATSAPP_API_URL');
        $this->token = env('WHATSAPP_API_TOKEN');
    }

    /**
     * Kirim notifikasi sukses pembayaran via WhatsApp
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

        // Jika menggunakan detail pesanan
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
     * Kirim pesan WhatsApp menggunakan API
     */
    private function sendMessage($phone, $message)
    {
        if (empty($this->apiUrl) || empty($this->token)) {
            Log::warning('WhatsApp API URL atau token belum dikonfigurasi');
            return false;
        }

        try {
            // Implementasi untuk integrasi dengan layanan WhatsApp API
            // Misalnya menggunakan Twilio, WhatSend, atau lainnya
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl, [
                'phone' => $phone,
                'message' => $message
            ]);

            if (!$response->successful()) {
                Log::error('WhatsApp API Error: ' . $response->body());
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('WhatsApp API Error: ' . $e->getMessage());
            return false;
        }
    }
}
