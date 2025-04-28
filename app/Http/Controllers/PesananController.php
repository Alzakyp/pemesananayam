<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\Pembayaran;
use App\Services\MidtransService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class PesananController extends Controller
{
    /**
     * Tampilkan daftar pesanan.
     */
    public function index()
    {
        $pesanans = Pesanan::with(['pelanggan', 'produk'])->get();
        return view('pesanan.index', compact('pesanans'));
    }

    /**
     * Tampilkan form tambah pesanan.
     */
    public function create()
    {
        $produks = Produk::all();
        $pelanggans = User::where('role', 'pelanggan')->get();
        return view('pesanan.create', compact('produks', 'pelanggans'));
    }

    /**
     * Simpan pesanan baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:users,id',
            'alamat_pengiriman' => 'required|string|max:255',
            'id_produk' => 'required|exists:produk,id',
            'total_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Midtrans,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
            'tanggal_pengiriman' => 'nullable|date|after_or_equal:today',
        ]);

        // Dapatkan alamat dari user jika alamat_pengiriman kosong
        if (empty($request->alamat_pengiriman)) {
            $user = User::find($request->id_pelanggan);
            if ($user && !empty($user->alamat)) {
                $request->merge(['alamat_pengiriman' => $user->alamat]);
            }
        }

        // Tentukan apakah ini adalah pengiriman di masa depan
        $isFutureDelivery = false;
        $tanggalPengiriman = null;

        if ($request->has('tanggal_pengiriman') && !empty($request->tanggal_pengiriman)) {
            $tanggalPengiriman = Carbon::parse($request->tanggal_pengiriman);
            $isFutureDelivery = $tanggalPengiriman->isAfter(Carbon::today());
        }

        // Buat pesanan baru
        $pesanan = Pesanan::create([
            'id_pelanggan' => $request->id_pelanggan,
            'alamat_pengiriman' => $request->alamat_pengiriman,
            'id_produk' => $request->id_produk,
            'total_bayar' => $request->total_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'metode_pengiriman' => $request->metode_pengiriman,
            'status' => 'Mempersiapkan',
            'tanggal_pemesanan' => now(),
            'tanggal_pengiriman' => $tanggalPengiriman,
            'stok_dikurangi' => !$isFutureDelivery, // Hanya kurangi stok langsung jika bukan di masa depan
        ]);

        // Kurangi stok produk jika bukan pengiriman masa depan
        if (!$isFutureDelivery) {
            $produk = Produk::find($request->id_produk);
            if ($produk) {
                $produk->stok -= 1; // Asumsi jumlah 1, atau gunakan nilai dari request
                $produk->save();
            }
        }

        // Jika menggunakan Midtrans
        if ($request->metode_pembayaran == 'Midtrans') {
            $midtransService = new MidtransService();
            $midtransResponse = $midtransService->createTransaction($pesanan);

            if ($midtransResponse['success']) {
                return redirect($midtransResponse['redirect_url'])
                    ->with('success', 'Pesanan berhasil dibuat. Silakan selesaikan pembayaran.');
            } else {
                return back()->with('error', 'Gagal membuat transaksi: ' . $midtransResponse['message']);
            }
        }
        // Jika pembayaran tunai
        else {
            // Buat entry pembayaran dengan status menunggu
            Pembayaran::create([
                'id_pesanan' => $pesanan->id,
                'metode' => 'Tunai',
                'status_pemrosesan' => 'Menunggu Pembayaran'
            ]);

            return redirect()->route('pesanan.index')
                ->with('success', 'Pesanan berhasil dibuat dengan pembayaran tunai.');
        }
    }

    /**
     * Tampilkan detail pesanan.
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['pelanggan', 'produk', 'detailPesanan.produk', 'pembayaran'])->findOrFail($id);
        return view('pesanan.show', compact('pesanan'));
    }

    /**
     * Tampilkan form edit pesanan.
     */
    public function edit($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $produks = Produk::all();
        $pelanggans = User::where('role', 'pelanggan')->get();

        return view('pesanan.edit', compact('pesanan', 'produks', 'pelanggans'));
    }

    /**
     * Update pesanan di database.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_pelanggan' => 'required|exists:users,id',
            'alamat_pengiriman' => 'required|string|max:255',
            'id_produk' => 'required|exists:produk,id',
            'total_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Midtrans,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
            'status' => 'required|in:Mempersiapkan,Proses pengantaran,Siap Diambil,Selesai,Dibatalkan',
            'tanggal_pengiriman' => 'nullable|date',
        ]);

        $pesanan = Pesanan::findOrFail($id);

        // Simpan status dan stok_dikurangi sebelumnya
        $oldStatus = $pesanan->status;
        $oldStokDikurangi = $pesanan->stok_dikurangi;

        // Update pesanan dengan data baru
        $pesanan->update($request->all());

        // Cek perubahan status untuk penanganan stok
        if ($oldStatus != $request->status) {
            // Jika status berubah menjadi 'Dibatalkan' dan stok sudah dikurangi, kembalikan stok
            if ($request->status == 'Dibatalkan' && $oldStokDikurangi) {
                $produk = Produk::find($pesanan->id_produk);
                if ($produk) {
                    $produk->stok += 1; // Asumsi jumlah 1, sesuaikan dengan jumlah actual
                    $produk->save();

                    // Update flag stok_dikurangi
                    $pesanan->stok_dikurangi = false;
                    $pesanan->save();
                }
            }
        }

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil diperbarui.');
    }

    /**
     * Hapus pesanan dari database.
     */
    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);

        // Jika stok sudah dikurangi, kembalikan stok
        if ($pesanan->stok_dikurangi) {
            $produk = Produk::find($pesanan->id_produk);
            if ($produk) {
                $produk->stok += 1; // Asumsi jumlah 1, sesuaikan jika perlu
                $produk->save();
            }
        }

        $pesanan->delete();

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil dihapus.');
    }

    /**
     * Update status pesanan.
     */
    public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:Mempersiapkan,Proses pengantaran,Siap Diambil,Selesai,Dibatalkan',
    ]);

    try {
        $pesanan = Pesanan::findOrFail($id);
        $oldStatus = $pesanan->status;

        // Menentukan status yang sesuai berdasarkan metode pengiriman jika status = Proses pengantaran
        if ($request->status == 'Proses pengantaran' && $pesanan->metode_pengiriman == 'Pick Up') {
            $pesanan->status = 'Siap Diambil';
        } else {
            $pesanan->status = $request->status;
        }

        $pesanan->save();

        // Jika pesanan dibatalkan dan stok sudah dikurangi, kembalikan stok
        if ($request->status == 'Dibatalkan' && $pesanan->stok_dikurangi) {
            // Jika menggunakan detail pesanan
            if (count($pesanan->detailPesanan) > 0) {
                foreach ($pesanan->detailPesanan as $detail) {
                    $produk = $detail->produk;
                    if ($produk) {
                        $produk->stok += $detail->jumlah;
                        $produk->save();
                    }
                }
            }
            // Jika pesanan langsung terhubung ke produk
            elseif ($pesanan->id_produk) {
                $produk = Produk::find($pesanan->id_produk);
                if ($produk) {
                    $produk->stok += $pesanan->jumlah;
                    $produk->save();
                }
            }

            $pesanan->stok_dikurangi = false;
            $pesanan->save();
        }

        // Kirim notifikasi WhatsApp hanya jika statusnya berubah dan merupakan pembayaran tunai
        // Untuk pembayaran Midtrans, notifikasi sudah ditangani oleh MidtransService
        if (($oldStatus != $pesanan->status) && $pesanan->metode_pembayaran == 'Tunai' &&
            in_array($pesanan->status, ['Proses pengantaran', 'Siap Diambil'])) {
            try {
                $whatsappService = new WhatsAppService();
                $whatsappService->sendCashOrderNotification($pesanan);
                FacadesLog::info("Cash order notification sent for order #{$pesanan->id} with status: {$pesanan->status}");
            } catch (\Exception $e) {
                FacadesLog::error("Failed to send WhatsApp notification for cash order #{$pesanan->id}: " . $e->getMessage());
            }
        }

        return redirect()->route('pesanan.show', $id)->with('success', 'Status pesanan berhasil diperbarui.');
    } catch (\Exception $e) {
        FacadesLog::error("Error updating order status: " . $e->getMessage());
        return redirect()->route('pesanan.show', $id)->with('error', 'Terjadi kesalahan saat memperbarui status pesanan: ' . $e->getMessage());
    }
}
}
