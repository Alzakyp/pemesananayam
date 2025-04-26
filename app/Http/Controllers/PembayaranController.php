<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar pembayaran untuk monitoring.
     */
    public function index()
    {
        $pembayaran = Pembayaran::with('pesanan.pelanggan')->latest()->get();
        return view('pembayaran.index', compact('pembayaran'));
    }

    /**
     * Menampilkan detail pembayaran.
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with(['pesanan.pelanggan', 'pesanan.detailPesanan.produk'])->findOrFail($id);
        return view('pembayaran.show', compact('pembayaran'));
    }

    /**
     * Menampilkan form edit pembayaran - HANYA untuk admin melihat status, bukan mengubah.
     */
    public function edit($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        return view('pembayaran.edit', compact('pembayaran'));
    }

    /**
     * Update HANYA untuk kasus khusus, sebaiknya tidak digunakan
     * karena status seharusnya diupdate otomatis lewat webhook
     */
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $request->validate([
            'status_pemrosesan' => 'required|in:Menunggu Pembayaran,Diproses,Selesai,Ditolak',
        ]);

        // Log update manual untuk audit
        Log::info('Pembayaran ID ' . $id . ' diupdate secara manual oleh admin.', [
            'old_status' => $pembayaran->status_pemrosesan,
            'new_status' => $request->status_pemrosesan,
        ]);
        $pembayaran->status_pemrosesan = $request->status_pemrosesan;
        $pembayaran->save();

        return redirect()->route('pembayaran.index')->with('success', 'Status pembayaran berhasil diperbarui (Manual Update)');
    }

    public function synchronizeStatus($id)
    {
        try {
            $pembayaran = Pembayaran::findOrFail($id);
            $pesanan = $pembayaran->pesanan;

            if (!$pesanan) {
                return redirect()->route('pembayaran.index')
                    ->with('error', 'Pesanan untuk pembayaran ini tidak ditemukan');
            }

            // Logika sinkronisasi
            if (($pesanan->status == 'Proses pengantaran' || $pesanan->status == 'Siap Diambil')
                && $pembayaran->status_pemrosesan == 'Menunggu Pembayaran'
            ) {
                $pembayaran->status_pemrosesan = 'Diproses';
                $pembayaran->tanggal_pembayaran = $pesanan->tanggal_pembayaran ?? now();
                $pembayaran->save();

                Log::info("Pembayaran ID {$pembayaran->id} status disinkronkan dengan pesanan ID {$pesanan->id}");

                return redirect()->route('pembayaran.index')
                    ->with('success', 'Status pembayaran berhasil disinkronkan dengan status pesanan');
            }
            return redirect()->route('pembayaran.index')
                ->with('info', 'Status pembayaran sudah sesuai dengan status pesanan');
        } catch (\Exception $e) {
            Log::error("Gagal sinkronisasi status pembayaran: " . $e->getMessage());

            return redirect()->route('pembayaran.index')
                ->with('error', 'Gagal sinkronisasi status pembayaran');
        }
    }

    /**
     * Hapus duplikasi pembayaran dan sinkronkan status
     */
    public function fixDuplicatePayments()
    {
        try {
            // Dapatkan semua id_pesanan yang memiliki lebih dari 1 pembayaran
            $duplicates = Pembayaran::select('id_pesanan')
                ->groupBy('id_pesanan')
                ->havingRaw('COUNT(*) > 1')
                ->get()
                ->pluck('id_pesanan');

            $fixed = 0;

            foreach ($duplicates as $id_pesanan) {
                // Ambil semua pembayaran untuk pesanan ini
                $payments = Pembayaran::where('id_pesanan', $id_pesanan)->get();
                $pesanan = Pesanan::find($id_pesanan);

                if (!$pesanan) continue;

                // Cari pembayaran dengan status terkini
                $latestPayment = $payments->sortByDesc('updated_at')->first();

                // Update status pembayaran sesuai status pesanan
                if (($pesanan->status == 'Proses pengantaran' || $pesanan->status == 'Siap Diambil') &&
                    $latestPayment->status_pemrosesan == 'Menunggu Pembayaran') {
                    $latestPayment->status_pemrosesan = 'Diproses';
                    $latestPayment->tanggal_pembayaran = $pesanan->tanggal_pembayaran ?? now();
                    $latestPayment->save();
                }

                // Hapus pembayaran duplikat kecuali yang terbaru
                foreach ($payments as $payment) {
                    if ($payment->id != $latestPayment->id) {
                        $payment->delete();
                        $fixed++;
                    }
                }
            }

            return redirect()->route('pembayaran.index')
                ->with('success', "Berhasil memperbaiki {$fixed} pembayaran duplikat");
        } catch (\Exception $e) {
            Log::error("Gagal memperbaiki duplikasi pembayaran: " . $e->getMessage());

            return redirect()->route('pembayaran.index')
                ->with('error', 'Gagal memperbaiki duplikasi pembayaran');
        }
    }
}
