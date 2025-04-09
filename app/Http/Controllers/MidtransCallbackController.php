<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Handle callback dari Midtrans
     */
    public function handle(Request $request)
    {
        $notification = json_decode($request->getContent());

        // Log callback untuk debugging
        Log::info('Midtrans callback received:', ['data' => $notification]);

        // Ambil data dari notifikasi
        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $order_id = $notification->order_id;
        $fraud = $notification->fraud_status ?? null;

        // Cari pembayaran berdasarkan ID transaksi Midtrans
        $pembayaran = Pembayaran::where('midtrans_transaction_id', $order_id)->first();

        if (!$pembayaran) {
            Log::error('Pembayaran tidak ditemukan untuk order_id: ' . $order_id);
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        }

        // Simpan status dari Midtrans
        $pembayaran->midtrans_status = $transaction;
        $pembayaran->midtrans_payment_type = $type;

        // Update status pemrosesan berdasarkan status Midtrans
        if ($transaction == 'capture') {
            if ($fraud == 'challenge') {
                $pembayaran->status_pemrosesan = 'Menunggu Pembayaran';
            } else if ($fraud == 'accept') {
                $this->processSuccessPayment($pembayaran);
            }
        } else if ($transaction == 'settlement') {
            $this->processSuccessPayment($pembayaran);
        } else if ($transaction == 'pending') {
            $pembayaran->status_pemrosesan = 'Menunggu Pembayaran';
        } else if (in_array($transaction, ['deny', 'cancel', 'expire'])) {
            $pembayaran->status_pemrosesan = 'Ditolak';
        }

        $pembayaran->save();

        return response()->json(['message' => 'Callback processed successfully']);
    }

    /**
     * Proses pembayaran sukses
     */
    private function processSuccessPayment($pembayaran)
    {
        $pembayaran->status_pemrosesan = 'Diproses';
        $pembayaran->tanggal_pembayaran = now();

        // Update status pesanan
        $pesanan = $pembayaran->pesanan;
        $pesanan->status = 'Diproses';
        $pesanan->save();

        // Kirim notifikasi WhatsApp
        try {
            $whatsappService = new WhatsAppService();
            $whatsappService->sendPaymentSuccessNotification($pesanan);
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi WhatsApp: ' . $e->getMessage());
        }
    }
}
