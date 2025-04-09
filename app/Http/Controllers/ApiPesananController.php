<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiPesananController extends Controller
{
    /**
     * Menampilkan semua pesanan berdasarkan id_pelanggan
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id_pelanggan = $request->query('id_pelanggan');

        if (!$id_pelanggan) {
            return response()->json([
                'success' => false,
                'message' => 'ID Pelanggan diperlukan',
            ], 400);
        }

        $pesanan = Pesanan::with(['produk', 'detailPesanan.produk'])
            ->where('id_pelanggan', $id_pelanggan)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Pesanan',
            'data' => $pesanan
        ], 200);
    }

    /**
     * Membuat pesanan baru (guest checkout atau dengan akun)
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi request
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'nullable|integer', // Optional untuk guest checkout
            'nama' => 'required_without:id_pelanggan|string|max:255', // Wajib jika tidak ada id_pelanggan
            'no_hp' => 'required_without:id_pelanggan|string|max:15', // Wajib jika tidak ada id_pelanggan
            'alamat_pengiriman' => 'required|string|max:255',
            'produk' => 'required|array|min:1', // Format: [{id_produk: 1, jumlah: 2, berat: 1.5}]
            'produk.*.id_produk' => 'required|exists:produk,id',
            'produk.*.jumlah' => 'required|integer|min:1',
            'produk.*.berat' => 'nullable|numeric|min:0.1',
            'metode_pembayaran' => 'required|in:Midtrans,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
            'lokasi_maps' => 'nullable|string', // Koordinat Google Maps
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        // Validasi stok produk
        foreach ($request->produk as $item) {
            $produk = Produk::find($item['id_produk']);

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
            foreach ($request->produk as $item) {
                $produk = Produk::find($item['id_produk']);
                $berat = isset($item['berat']) ? $item['berat'] : 1;
                $subtotal = $produk->harga * $item['jumlah'] * $berat;
                $total_bayar += $subtotal;
            }

            // Buat pesanan baru
            $pesanan = Pesanan::create([
                'id_pelanggan' => $request->id_pelanggan,
                'nama' => $request->nama,
                'no_hp' => $request->no_hp,
                'alamat_pengiriman' => $request->alamat_pengiriman,
                'total_bayar' => $total_bayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'metode_pengiriman' => $request->metode_pengiriman,
                'lokasi_maps' => $request->lokasi_maps,
                'status' => 'Menunggu Konfirmasi',
                'tanggal_pemesanan' => now(),
            ]);

            // Simpan detail pesanan
            foreach ($request->produk as $item) {
                $produk = Produk::find($item['id_produk']);
                $berat = isset($item['berat']) ? $item['berat'] : 1;
                $subtotal = $produk->harga * $item['jumlah'] * $berat;

                DetailPesanan::create([
                    'id_pesanan' => $pesanan->id,
                    'id_produk' => $item['id_produk'],
                    'jumlah' => $item['jumlah'],
                    'berat' => $berat,
                    'harga' => $produk->harga,
                    'subtotal' => $subtotal
                ]);

                // Kurangi stok produk
                $produk->stok -= $item['jumlah'];
                $produk->save();
            }

            // Membuat entry pembayaran
            if ($request->metode_pembayaran == 'Midtrans') {
                $pembayaran = Pembayaran::create([
                    'id_pesanan' => $pesanan->id,
                    'metode' => 'Midtrans',
                    'status_pemrosesan' => 'Menunggu Pembayaran'
                ]);

                // Generate payment URL dengan Midtrans
                $midtransService = new MidtransService();
                $midtransResponse = $midtransService->createTransaction($pesanan);

                if (!$midtransResponse['success']) {
                    throw new \Exception('Gagal membuat transaksi Midtrans: ' . $midtransResponse['message']);
                }

                $payment_url = $midtransResponse['redirect_url'];
            } else {
                // Pembayaran Tunai
                Pembayaran::create([
                    'id_pesanan' => $pesanan->id,
                    'metode' => 'Tunai',
                    'status_pemrosesan' => 'Menunggu Pembayaran'
                ]);

                $payment_url = null;
            }

            DB::commit();

            // Load relasi detail pesanan dan produk
            $pesanan->load('detailPesanan.produk');

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat',
                'data' => [
                    'pesanan' => $pesanan,
                    'payment_url' => $payment_url
                ]
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan detail pesanan
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['detailPesanan.produk', 'pembayaran'])
            ->find($id);

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Pesanan',
            'data' => $pesanan
        ], 200);
    }

    /**
     * Membatalkan pesanan
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function cancelOrder($id)
    {
        $pesanan = Pesanan::with('detailPesanan')->find($id);

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        // Cek apakah pesanan masih bisa dibatalkan
        if (!in_array($pesanan->status, ['Menunggu Konfirmasi', 'Diproses'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak dapat dibatalkan',
            ], 400);
        }

        DB::beginTransaction();

        try {
            // Kembalikan stok produk
            foreach ($pesanan->detailPesanan as $detail) {
                $produk = Produk::find($detail->id_produk);
                $produk->stok += $detail->jumlah;
                $produk->save();
            }

            // Update status pesanan
            $pesanan->status = 'Dibatalkan';
            $pesanan->save();

            // Update status pembayaran jika ada
            if ($pesanan->pembayaran) {
                $pesanan->pembayaran->status_pemrosesan = 'Ditolak';
                $pesanan->pembayaran->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan',
                'data' => $pesanan
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check status pesanan
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function checkStatus($id)
    {
        $pesanan = Pesanan::with('pembayaran')->find($id);

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status Pesanan',
            'data' => [
                'id_pesanan' => $pesanan->id,
                'status_pesanan' => $pesanan->status,
                'status_pembayaran' => $pesanan->pembayaran ? $pesanan->pembayaran->status_pemrosesan : null,
                'metode_pembayaran' => $pesanan->metode_pembayaran,
                'total_bayar' => $pesanan->total_bayar
            ]
        ], 200);
    }
}
