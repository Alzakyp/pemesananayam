<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiPesananController extends Controller
{
    /**
     * Store a newly created order
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'required|integer', // Changed from 'required|exists:users,id'
            'alamat_pengiriman' => 'required|string|max:255',
            'id_produk' => 'required|exists:produk,id',
            'total_bayar' => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:Transfer,Tunai',
            'metode_pengiriman' => 'required|in:Delivery,Pick Up',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $pesanan = Pesanan::create([
            'id_pelanggan' => $request->id_pelanggan,
            'alamat_pengiriman' => $request->alamat_pengiriman,
            'id_produk' => $request->id_produk,
            'total_bayar' => $request->total_bayar,
            'metode_pembayaran' => $request->metode_pembayaran,
            'metode_pengiriman' => $request->metode_pengiriman,
            'status' => 'Menunggu Konfirmasi', // Default status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'data' => $pesanan
        ], 201);
    }
}
