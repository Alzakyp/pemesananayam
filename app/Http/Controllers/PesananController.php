<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Http\Request;

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
     * Tampilkan form untuk menambah pesanan.
     */
    public function create()
    {
        // Ambil data produk dan pelanggan untuk dipilih di form
        $produks = Produk::all(); // Asumsikan ada model Produk
        $pelanggans = User::where('role', 'pelanggan')->get(); // Asumsikan ada role pelanggan

        // Tambahkan dd untuk melihat data
        // dd($pelanggans);

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
            'metode_pembayaran' => 'required|in:Transfer,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
        ]);

        // Pesanan::create($request->all());
        Pesanan::create([
            'id_pelanggan' => $request->id_pelanggan,
            'alamat_pengiriman' => $request->alamat_pengiriman,
            'id_produk' => $request->id_produk,
            'total_bayar' => $request->total_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'metode_pengiriman' => $request->metode_pengiriman,
            'status' => 'Menunggu Konfirmasi', // Default status
        ]);

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail pesanan.
     */
    public function show($id)
    {
        $pesanan = Pesanan::with(['pelanggan', 'produk'])->findOrFail($id);
        return view('pesanan.show', compact('pesanan'));
    }

    /**
     * Tampilkan form edit pesanan.
     */
    public function edit($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $produks = Produk::all(); // Ambil semua produk
        $pelanggans = User::where('role', 'pelanggan')->get(); // Ambil pelanggan

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
            'metode_pembayaran' => 'required|in:Transfer,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
        ]);

        $pesanan = Pesanan::findOrFail($id);
        $pesanan->update($request->all());

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil diperbarui.');
    }

    /**
     * Hapus pesanan dari database.
     */
    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return redirect()->route('pesanan.index')->with('success', 'Pesanan berhasil dihapus.');
    }
}
