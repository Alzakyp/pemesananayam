<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiProdukController extends Controller
{
    /**
     * Menampilkan semua produk
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Ambil semua produk dengan stok > 0
        $produk = Produk::where('stok', '>', 0)->get();

        // Tambahkan URL gambar lengkap ke setiap produk
        $produk->map(function ($item) {
            if ($item->gambar) {
                $item->gambar_url = asset('storage/' . $item->gambar);
            } else {
                $item->gambar_url = null;
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar Produk',
            'data' => $produk
        ], 200);
    }

    /**
     * Tampilkan produk berdasarkan kategori (ayam mentah/olahan)
     *
     * @param string $kategori
     * @return \Illuminate\Http\Response
     */
    public function getByKategori($kategori)
    {
        // Kategori: 'mentah' untuk ayam mentah (kg/ekor), 'olahan' untuk menu olahan (pcs)
        if ($kategori == 'mentah') {
            $produk = Produk::where('stok', '>', 0)
                ->whereIn('satuan', ['Kg', 'ekor'])
                ->get();
        } elseif ($kategori == 'olahan') {
            $produk = Produk::where('stok', '>', 0)
                ->where('satuan', 'pcs')
                ->get();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kategori tidak valid',
            ], 400);
        }

        // Tambahkan URL gambar lengkap
        $produk->map(function ($item) {
            if ($item->gambar) {
                $item->gambar_url = asset('storage/' . $item->gambar);
            } else {
                $item->gambar_url = null;
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar Produk ' . ucfirst($kategori),
            'data' => $produk
        ], 200);
    }

    /**
     * Menampilkan detail produk
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        // Tambahkan URL gambar lengkap
        if ($produk->gambar) {
            $produk->gambar_url = asset('storage/' . $produk->gambar);
        } else {
            $produk->gambar_url = null;
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Produk',
            'data' => $produk
        ], 200);
    }

    /**
     * Cari produk berdasarkan nama
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $keyword = $request->query('keyword');

        if (!$keyword) {
            return $this->index();
        }

        $produk = Produk::where('nama_produk', 'like', "%{$keyword}%")
            ->where('stok', '>', 0)
            ->get();

        // Tambahkan URL gambar lengkap
        $produk->map(function ($item) {
            if ($item->gambar) {
                $item->gambar_url = asset('storage/' . $item->gambar);
            } else {
                $item->gambar_url = null;
            }
            return $item;
        });

        return response()->json([
            'success' => true,
            'message' => 'Hasil Pencarian Produk',
            'data' => $produk
        ], 200);
    }
}
