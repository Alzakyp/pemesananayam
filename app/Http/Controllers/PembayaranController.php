<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    /**
     * Menampilkan daftar pembayaran.
     */
    public function index()
    {
        $pembayaran = Pembayaran::with('pesanan')->latest()->get();
        return view('pembayaran.index', compact('pembayaran'));
    }

    /**
     * Menampilkan form tambah pembayaran.
     */
    public function create()
    {
        $pesanan = Pesanan::all(); // Ambil semua pesanan
        return view('pembayaran.create', compact('pesanan'));
    }

    /**
     * Menyimpan pembayaran baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id',
            'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:Menunggu Verifikasi,Terverifikasi,Ditolak',
        ]);

        $buktiTransferPath = null;
        if ($request->hasFile('bukti_transfer')) {
            $buktiTransferPath = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
        }

        Pembayaran::create([
            'id_pesanan' => $request->id_pesanan,
            'bukti_transfer' => $buktiTransferPath,
            'status' => $request->status,
        ]);

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail pembayaran.
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with('pesanan')->findOrFail($id);
        return view('pembayaran.show', compact('pembayaran'));
    }

    /**
     * Menampilkan form edit pembayaran.
     */
    public function edit($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);
        $pesanan = Pesanan::all();
        return view('pembayaran.edit', compact('pembayaran', 'pesanan'));
    }

    /**
     * Mengupdate data pembayaran.
     */
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $request->validate([
            'id_pesanan' => 'required|exists:pesanan,id',
            //'bukti_transfer' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:Menunggu Verifikasi,Terverifikasi,Ditolak',
        ]);

        // if ($request->hasFile('bukti_transfer')) {
            // Hapus bukti transfer lama jika ada
        //     if ($pembayaran->bukti_transfer) {
        //         Storage::disk('public')->delete($pembayaran->bukti_transfer);
        //     }
        //     $pembayaran->bukti_transfer = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
        // }

        $pembayaran->id_pesanan = $request->id_pesanan;
        $pembayaran->status = $request->status;
        $pembayaran->save();

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil diperbarui.');
    }

    /**
     * Menghapus data pembayaran.
     */
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        // Hapus bukti transfer jika ada
        if ($pembayaran->bukti_transfer) {
            Storage::disk('public')->delete($pembayaran->bukti_transfer);
        }

        $pembayaran->delete();

        return redirect()->route('pembayaran.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}
