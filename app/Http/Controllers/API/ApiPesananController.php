<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Models\Produk;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApiPesananController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Buat pesanan baru untuk pelanggan yang login (bisa hari H atau H+)
     */
    public function store(Request $request)
    {
        // Validasi request
        $request->validate([
            'id_pelanggan' => 'required',
            'nama' => 'required|string|max:255',
            'no_hp' => 'required|string|max:15',
            'alamat_pengiriman' => 'required',
            'metode_pembayaran' => 'required',
            'metode_pengiriman' => 'required',
            'lokasi_maps' => 'nullable|string',
            'tanggal_pengiriman' => 'required|date|date_format:Y-m-d',
            'detail_pesanan' => 'required|array',
            'detail_pesanan.*.id_produk' => 'required|exists:produk,id',
            'detail_pesanan.*.jumlah' => 'required|integer|min:1',
        ]);

        // Ambil ID pelanggan dari user yang login
        $id_pelanggan = $request->id_pelanggan;

        // Jika user tidak login, return error
        if (!$id_pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi'
            ], 401);
        }

        // Validasi tanggal pengiriman (untuk pelanggan login, bisa H atau H+)
        $tanggal_pengiriman = Carbon::parse($request->tanggal_pengiriman);
        $today = Carbon::today();

        // Tanggal tidak boleh di masa lalu
        if ($tanggal_pengiriman->lt($today)) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal pengiriman tidak boleh di masa lalu'
            ], 400);
        }

        // Validasi max pemesanan 7 hari ke depan (sesuaikan dengan kebutuhan)
        if ($tanggal_pengiriman->diffInDays($today) > 7) {
            return response()->json([
                'success' => false,
                'message' => 'Pemesanan maksimal untuk 7 hari ke depan'
            ], 400);
        }

        // Ambil semua produk sekaligus untuk mengurangi query
        $produkIds = collect($request->detail_pesanan)->pluck('id_produk');
        $produkList = Produk::whereIn('id', $produkIds)->get()->keyBy('id');

        // Validasi stok produk
        foreach ($request->detail_pesanan as $item) {
            $produk = $produkList[$item['id_produk']] ?? null;

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dengan ID ' . $item['id_produk'] . ' tidak ditemukan'
                ], 404);
            }

            if ($produk->stok < $item['jumlah']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk produk: ' . $produk->nama_produk,
                    'available_stock' => $produk->stok
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Hitung total bayar dari semua produk
            $total_bayar = 0;
            foreach ($request->detail_pesanan as $item) {
                $produk = $produkList[$item['id_produk']];
                $subtotal = $produk->harga * $item['jumlah'];
                $total_bayar += $subtotal;
            }

            // Generate order ID untuk Midtrans
            $dateTime = now();
            $orderId = 'SIAYAM-USER-' . $dateTime->format('Ymd') . '-' . $dateTime->format('His') . '-' . $id_pelanggan;

            // Status awal pesanan - akan diubah jika tunai
            $initialStatus = 'Mempersiapkan';

            // Jika pembayaran tunai, tentukan status berdasarkan metode pengiriman
            if ($request->metode_pembayaran == 'Tunai') {
                if ($request->metode_pengiriman == 'Delivery') {
                    $initialStatus = 'Proses pengantaran';
                } else {
                    $initialStatus = 'Siap Diambil';
                }
            }

            // Buat pesanan baru
            $pesanan = Pesanan::create([
                'id_pelanggan' => $id_pelanggan,
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'total_bayar' => $total_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'metode_pengiriman' => $request->metode_pengiriman,
                'lokasi_maps' => $request->lokasi_maps ?? null,
                'status' => $initialStatus,
                'tanggal_pemesanan' => now(),
                'tanggal_pengiriman' => $tanggal_pengiriman,
                'midtrans_order_id' => $orderId,
                'stok_dikurangi' => true,
                'tanggal_pembayaran' => $request->metode_pembayaran == 'Tunai' ? now() : null,
            ]);

            // Simpan detail pesanan
            foreach ($request->detail_pesanan as $item) {
                $produk = $produkList[$item['id_produk']];
                $subtotal = $produk->harga * $item['jumlah'];

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $produk->harga,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok produk
                $produk->stok -= $item['jumlah'];
                $produk->save();
            }

            // Jika menggunakan Midtrans, buat transaksi pembayaran
            $paymentResponse = null;
            if ($request->metode_pembayaran == 'Midtrans') {
                $paymentResponse = $this->midtransService->createTransaction($pesanan);

                if (!$paymentResponse['success']) {
                    throw new \Exception('Gagal membuat transaksi Midtrans: ' . ($paymentResponse['message'] ?? 'Unknown error'));
                }

                // Buat record pembayaran melalui MidtransService (status: Menunggu Pembayaran)
                $this->midtransService->createPaymentRecord($pesanan, $paymentResponse);
            } else {
                // Buat record pembayaran untuk metode tunai dengan status Diproses
                Pembayaran::create([
                    'id_pesanan' => $pesanan->id,
                    'metode' => 'Tunai',
                    'status_pemrosesan' => 'Diproses',
                    'tanggal_pembayaran' => now(),
                    'snap_token' => null,
                    'snap_url' => null,
                    'midtrans_order_id' => $orderId
                ]);
            }

            DB::commit();

            // Log pesanan berhasil dibuat
            Log::info('Pesanan berhasil dibuat', [
                'pesanan_id' => $pesanan->id,
                'order_id' => $pesanan->midtrans_order_id,
                'id_pelanggan' => $id_pelanggan,
                'tanggal_pengiriman' => $tanggal_pengiriman->format('Y-m-d'),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $pesanan->status
            ]);

            $responseData = [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'id_pesanan' => $pesanan->id,
                    'total_bayar' => $pesanan->total_bayar,
                    'status' => $pesanan->status,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                    'tanggal_pengiriman' => $tanggal_pengiriman->format('Y-m-d')
                ]
            ];

            // Tambahkan informasi pembayaran Midtrans jika ada
            if ($paymentResponse && isset($paymentResponse['snap_token'])) {
                $responseData['data']['payment'] = [
                    'snap_token' => $paymentResponse['snap_token'],
                    'redirect_url' => $paymentResponse['redirect_url'],
                    'order_id' => $paymentResponse['order_id']
                ];
            }

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Log error
            Log::error('Gagal membuat pesanan: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buat pesanan untuk guest (tidak perlu login) - HANYA UNTUK HARI H
     */
    public function guestStore(Request $request)
    {
        // Validasi request
        $request->validate([
            'nama' => 'required|string',
            'no_hp' => 'required|string',
            'alamat_pengiriman' => 'required|string',
            'metode_pembayaran' => 'required|string|in:Midtrans,Tunai',
            'metode_pengiriman' => 'required|string|in:Delivery,Pick Up',
            'lokasi_maps' => 'nullable|string',
            'detail_pesanan' => 'required|array',
            'detail_pesanan.*.id_produk' => 'required|integer|exists:produk,id',
            'detail_pesanan.*.jumlah' => 'required|integer|min:1',
        ]);

        // Guest hanya bisa pesan untuk hari ini (hari H) - set otomatis
        $tanggal_pengiriman = Carbon::today();

        // Ambil semua produk sekaligus
        $produkIds = collect($request->detail_pesanan)->pluck('id_produk');
        $produkList = Produk::whereIn('id', $produkIds)->get()->keyBy('id');

        // Validasi stok
        foreach ($request->detail_pesanan as $item) {
            $produk = $produkList[$item['id_produk']] ?? null;

            if (!$produk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk dengan ID ' . $item['id_produk'] . ' tidak ditemukan'
                ], 404);
            }

            if ($produk->stok < $item['jumlah']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok tidak mencukupi untuk produk: ' . $produk->nama_produk,
                    'available_stock' => $produk->stok
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Hitung total bayar
            $total_bayar = 0;
            foreach ($request->detail_pesanan as $item) {
                $produk = $produkList[$item['id_produk']];
                $subtotal = $produk->harga * $item['jumlah'];
                $total_bayar += $subtotal;
            }

            // Generate order ID untuk guest
            $dateTime = now();
            $orderId = 'SIAYAM-GUEST-' . $dateTime->format('Ymd') . '-' . $dateTime->format('His') . '-G';

            // Status awal pesanan - akan diubah jika tunai
            $initialStatus = 'Mempersiapkan';

            // Jika pembayaran tunai, tentukan status berdasarkan metode pengiriman
            if ($request->metode_pembayaran == 'Tunai') {
                if ($request->metode_pengiriman == 'Delivery') {
                    $initialStatus = 'Proses pengantaran';
                } else {
                    $initialStatus = 'Siap Diambil';
                }
            }

            // Buat pesanan guest
            $pesanan = Pesanan::create([
                'id_pelanggan' => null, // Guest tidak memiliki id_pelanggan
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'total_bayar' => $total_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'metode_pengiriman' => $request->metode_pengiriman,
                'lokasi_maps' => $request->lokasi_maps ?? null,
                'status' => $initialStatus,
                'tanggal_pemesanan' => now(),
                'tanggal_pengiriman' => $tanggal_pengiriman, // Selalu hari ini
                'midtrans_order_id' => $orderId,
                'stok_dikurangi' => true,
                'tanggal_pembayaran' => $request->metode_pembayaran == 'Tunai' ? now() : null,
            ]);

            // Simpan detail pesanan
            foreach ($request->detail_pesanan as $item) {
                $produk = $produkList[$item['id_produk']];
                $subtotal = $produk->harga * $item['jumlah'];

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'harga' => $produk->harga,
                    'subtotal' => $subtotal,
                ]);

                // Kurangi stok
                $produk->stok -= $item['jumlah'];
                $produk->save();
            }

            // Proses pembayaran
            $paymentResponse = null;
            if ($request->metode_pembayaran == 'Midtrans') {
                $paymentResponse = $this->midtransService->createTransaction($pesanan);

                if (!$paymentResponse['success']) {
                    throw new \Exception('Gagal membuat transaksi Midtrans: ' . ($paymentResponse['message'] ?? 'Unknown error'));
                }

                // Buat record pembayaran melalui MidtransService (status: Menunggu Pembayaran)
                $this->midtransService->createPaymentRecord($pesanan, $paymentResponse);
            } else {
                // Buat record pembayaran untuk metode tunai dengan status Diproses
                Pembayaran::create([
                    'id_pesanan' => $pesanan->id,
                    'metode' => 'Tunai',
                    'status_pemrosesan' => 'Diproses',
                    'tanggal_pembayaran' => now(),
                    'snap_token' => null,
                    'snap_url' => null,
                    'midtrans_order_id' => $orderId
                ]);
            }

            DB::commit();

            // Log pesanan guest berhasil dibuat
            Log::info('Pesanan guest berhasil dibuat', [
                'pesanan_id' => $pesanan->id,
                'order_id' => $orderId,
                'nama' => $pesanan->nama,
                'tanggal_pengiriman' => $tanggal_pengiriman->format('Y-m-d'),
                'metode_pembayaran' => $request->metode_pembayaran,
                'status' => $pesanan->status
            ]);

            $responseData = [
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'id_pesanan' => $pesanan->id,
                    'total_bayar' => $pesanan->total_bayar,
                    'status' => $pesanan->status,
                    'metode_pembayaran' => $pesanan->metode_pembayaran,
                    'tanggal_pengiriman' => $tanggal_pengiriman->format('Y-m-d')
                ]
            ];

            // Tambahkan informasi pembayaran Midtrans jika ada
            if ($paymentResponse && isset($paymentResponse['snap_token'])) {
                $responseData['data']['payment'] = [
                    'snap_token' => $paymentResponse['snap_token'],
                    'redirect_url' => $paymentResponse['redirect_url'],
                    'order_id' => $paymentResponse['order_id']
                ];
            }

            return response()->json($responseData, 201);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Gagal membuat pesanan guest: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }
}
