<?php

namespace App\Http\Controllers;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;

class DetailPesananController extends Controller
{
    /**
     * Tampilkan daftar detail pesanan untuk pesanan tertentu.
     */
    public function index($idPesanan)
    {
        $pesanan = Pesanan::findOrFail($idPesanan);
        $detailPesanan = DetailPesanan::where('id_pesanan', $idPesanan)->get();

        return view('detail-pesanan.index', compact('pesanan', 'detailPesanan'));
    }

    /**
     * Tampilkan form untuk menambah detail pesanan.
     */
    public function create($idPesanan)
    {
        $pesanan = Pesanan::findOrFail($idPesanan);
        $produk = Produk::where('stok', '>', 0)->get();

        return view('detail-pesanan.create', compact('pesanan', 'produk'));
    }

    /**
     * Simpan detail pesanan baru ke database.
     */
    public function store(Request $request, $idPesanan)
    {
        $request->validate([
            'id_produk' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'berat' => 'nullable|numeric|min:0',
        ]);

        $pesanan = Pesanan::findOrFail($idPesanan);
        $produk = Produk::findOrFail($request->id_produk);

        // Cek stok
        if ($request->jumlah > $produk->stok) {
            return back()->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $produk->stok);
        }

        // Hitung subtotal
        $subtotal = $produk->harga * $request->jumlah * ($request->berat ?: 1);

        // Simpan detail pesanan
        $detailPesanan = DetailPesanan::create([
            'id_pesanan' => $idPesanan,
            'id_produk' => $request->id_produk,
            'jumlah' => $request->jumlah,
            'berat' => $request->berat,
            'harga' => $produk->harga,
            'subtotal' => $subtotal
        ]);

        // Update total pesanan
        $totalPesanan = DetailPesanan::where('id_pesanan', $idPesanan)->sum('subtotal');
        $pesanan->total_bayar = $totalPesanan;
        $pesanan->save();

        // Update stok produk
        $produk->stok -= $request->jumlah;
        $produk->save();

        return redirect()->route('pesanan.detail', $idPesanan)
            ->with('success', 'Produk berhasil ditambahkan ke pesanan.');
    }

    /**
     * Tampilkan form edit detail pesanan.
     */
    public function edit($idPesanan, $idDetail)
    {
        $pesanan = Pesanan::findOrFail($idPesanan);
        $detailPesanan = DetailPesanan::findOrFail($idDetail);
        $produk = Produk::all();

        return view('detail-pesanan.edit', compact('pesanan', 'detailPesanan', 'produk'));
    }

    /**
     * Update detail pesanan di database.
     */
    public function update(Request $request, $idPesanan, $idDetail)
    {
        $request->validate([
            'jumlah' => 'required|integer|min:1',
            'berat' => 'nullable|numeric|min:0',
        ]);

        $detailPesanan = DetailPesanan::findOrFail($idDetail);
        $produk = Produk::findOrFail($detailPesanan->id_produk);

        // Hitung stok yang tersedia (stok saat ini + jumlah saat ini)
        $stokTersedia = $produk->stok + $detailPesanan->jumlah;

        // Cek stok
        if ($request->jumlah > $stokTersedia) {
            return back()->with('error', 'Stok tidak mencukupi. Stok tersedia: ' . $stokTersedia);
        }

        // Update stok produk (kembalikan stok lama dan kurangi dengan jumlah baru)
        $produk->stok = $stokTersedia - $request->jumlah;
        $produk->save();

        // Hitung subtotal baru
        $subtotal = $detailPesanan->harga * $request->jumlah * ($request->berat ?: 1);

        // Update detail pesanan
        $detailPesanan->jumlah = $request->jumlah;
        $detailPesanan->berat = $request->berat;
        $detailPesanan->subtotal = $subtotal;
        $detailPesanan->save();

        // Update total pesanan
        $pesanan = Pesanan::findOrFail($idPesanan);
        $totalPesanan = DetailPesanan::where('id_pesanan', $idPesanan)->sum('subtotal');
        $pesanan->total_bayar = $totalPesanan;
        $pesanan->save();

        return redirect()->route('pesanan.detail', $idPesanan)
            ->with('success', 'Detail pesanan berhasil diperbarui.');
    }

    /**
     * Hapus detail pesanan dari database.
     */
    public function destroy($idPesanan, $idDetail)
    {
        $detailPesanan = DetailPesanan::findOrFail($idDetail);

        // Kembalikan stok
        $produk = Produk::findOrFail($detailPesanan->id_produk);
        $produk->stok += $detailPesanan->jumlah;
        $produk->save();

        // Hapus detail pesanan
        $detailPesanan->delete();

        // Update total pesanan
        $pesanan = Pesanan::findOrFail($idPesanan);
        $totalPesanan = DetailPesanan::where('id_pesanan', $idPesanan)->sum('subtotal');
        $pesanan->total_bayar = $totalPesanan;
        $pesanan->save();

        return redirect()->route('pesanan.detail', $idPesanan)
            ->with('success', 'Produk berhasil dihapus dari pesanan.');
    }
}
