<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // Pastikan untuk mengimpor Storage

class ProdukController extends Controller
{
    /**
     * Tampilkan daftar produk.
     */
    public function index()
    {
        $produk = Produk::all();
        return view('produk.index', compact('produk'));
    }

    /**
     * Tampilkan form untuk menambah produk.
     */
    public function create()
    {
        return view('produk.create');
    }

    /**
     * Simpan produk baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|in:Kg,pcs,ons,ekor', // Validasi untuk satuan
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Upload gambar jika ada
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('produk', 'public');
        }

        Produk::create([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Tampilkan detail produk.
     */
    public function show($id)
    {
        $produk = Produk::findOrFail($id);
        return view('produk.show', compact('produk'));
    }

    /**
     * Tampilkan form edit produk.
     */
    public function edit($id)
    {
        $produk = Produk::findOrFail($id);
        return view('produk.edit', compact('produk'));
    }

    /**
     * Update produk di database.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|in:Kg,pcs,ons,ekor', // Validasi untuk satuan
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $produk = Produk::findOrFail($id);

        // Update gambar jika ada upload baru
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($produk->gambar) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $gambarPath = $request->file('gambar')->store('produk', 'public');
            $produk->gambar = $gambarPath; // Simpan path gambar baru
        }

        // Update data produk lainnya
        $produk->update([
            'nama_produk' => $request->nama_produk,
            'harga' => $request->harga,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            // Jangan simpan 'gambar' di sini, sudah di atas
        ]);

        return redirect()->route('produk.index')->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Hapus produk dari database.
     */
    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);

        // Hapus gambar jika ada
        if ($produk->gambar) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();
        return redirect()->route('produk.index')->with('success', 'Produk berhasil dihapus.');
    }
}
