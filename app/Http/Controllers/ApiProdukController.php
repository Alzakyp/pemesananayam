<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiProdukController extends Controller
{
    /**
     * Display a listing of products
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $produk = Produk::all();

        $produk->each(function ($item) {
            $item->satuan = (int)$item->satuan;
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar Produk',
            'data' => $produk
        ], 200);
    }

    /**
     * Display the specified product
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

        return response()->json([
            'success' => true,
            'message' => 'Detail Produk',
            'data' => $produk
        ], 200);
    }

    /**
     * Store a newly created product
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric',
            'stok' => 'required|integer',
            'gambar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $produk = Produk::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dibuat',
            'data' => $produk
        ], 201);
    }

    /**
     * Update the specified product
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_produk' => 'string|max:255',
            'deskripsi' => 'string',
            'harga' => 'numeric',
            'stok' => 'integer',
            'gambar' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $produk->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $produk
        ], 200);
    }

    /**
     * Remove the specified product
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan',
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus',
        ], 200);
    }
}
