<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
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
}
