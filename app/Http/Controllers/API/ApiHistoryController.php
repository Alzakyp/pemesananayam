<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApiHistoryController extends Controller
{
    /**
     * Get order history for the authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();

        // Base query to get orders for the user with all related data
        $query = Pesanan::with(['pembayaran', 'detailPesanan.produk'])
            ->where('id_pelanggan', $user->id)
            ->orderBy('tanggal_pemesanan', 'desc');

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('tanggal_pemesanan', [$request->from_date, $request->to_date]);
        }

        // Paginate results
        $perPage = $request->input('per_page', 10);
        $pesanan = $query->paginate($perPage);

        // Transform data for response
        $formattedPesanan = $pesanan->map(function ($order) {
            // Get product details
            $products = [];

            if ($order->detailPesanan && $order->detailPesanan->count() > 0) {
                foreach ($order->detailPesanan as $detail) {
                    $products[] = [
                        'id' => $detail->id_produk,
                        'nama_produk' => $detail->produk->nama_produk ?? 'Produk tidak ditemukan',
                        'jumlah' => $detail->jumlah,
                        'harga' => (int)$detail->harga,
                        'subtotal' => (int)$detail->subtotal
                    ];
                }
            } else if ($order->id_produk && $order->produk) {
                // Handle case where pesanan directly links to a product
                $products[] = [
                    'id' => $order->id_produk,
                    'nama_produk' => $order->produk->nama_produk ?? 'Produk tidak ditemukan',
                    'jumlah' => $order->jumlah,
                    'harga' => (int)($order->total_bayar / ($order->jumlah ?: 1)),
                    'subtotal' => (int)$order->total_bayar
                ];
            }

            return [
                'id' => $order->id,
                'tanggal_pemesanan' => $order->tanggal_pemesanan,
                'tanggal_pengiriman' => $order->tanggal_pengiriman,
                'total_bayar' => (int)$order->total_bayar,
                'status' => $order->status,
                'metode_pembayaran' => $order->metode_pembayaran,
                'metode_pengiriman' => $order->metode_pengiriman,
                'payment_status' => $order->pembayaran ? $order->pembayaran->status_pemrosesan : 'Belum Ada Pembayaran',
                'is_completed' => in_array($order->status, ['Selesai']),
                'is_in_process' => in_array($order->status, ['Mempersiapkan', 'Proses pengantaran', 'Siap Diambil']),
                'is_cancelled' => $order->status === 'Dibatalkan',
                'products' => $products
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat pesanan berhasil diambil',
            'data' => $formattedPesanan,
            'meta' => [
                'current_page' => $pesanan->currentPage(),
                'last_page' => $pesanan->lastPage(),
                'per_page' => $pesanan->perPage(),
                'total' => $pesanan->total()
            ]
        ]);
    }

    /**
     * Get details of a specific order
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $pesanan = Pesanan::with(['detailPesanan.produk', 'pembayaran'])
            ->where('id', $id)
            ->where('id_pelanggan', $user->id)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan'
            ], 404);
        }

        // Format response data
        $orderDetails = [];

        foreach ($pesanan->detailPesanan as $detail) {
            $orderDetails[] = [
                'produk_id' => $detail->id_produk,
                'nama_produk' => $detail->produk->nama_produk,
                'jumlah' => $detail->jumlah,
                'harga' => (int)$detail->harga,
                'subtotal' => (int)$detail->subtotal
            ];
        }

        // Get payment details
        $paymentDetails = null;
        if ($pesanan->pembayaran) {
            $paymentDetails = [
                'status' => $pesanan->pembayaran->status_pemrosesan,
                'metode' => $pesanan->pembayaran->metode,
                'tanggal_pembayaran' => $pesanan->pembayaran->tanggal_pembayaran,
                'payment_details' => $pesanan->payment_details ? json_decode($pesanan->payment_details) : null
            ];
        }

        $result = [
            'id' => $pesanan->id,
            'tanggal_pemesanan' => $pesanan->tanggal_pemesanan,
            'tanggal_pengiriman' => $pesanan->tanggal_pengiriman,
            'total_bayar' => (int)$pesanan->total_bayar,
            'status' => $pesanan->status,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'metode_pengiriman' => $pesanan->metode_pengiriman,
            'nama_penerima' => $pesanan->nama,
            'no_hp' => $pesanan->no_hp,
            'alamat_pengiriman' => $pesanan->alamat_pengiriman,
            'lokasi_maps' => $pesanan->lokasi_maps,
            'items' => $orderDetails,
            'payment' => $paymentDetails,
            'tracking' => [
                'current_status' => $pesanan->status,
                'steps' => [
                    [
                        'status' => 'Mempersiapkan',
                        'completed' => true,
                        'timestamp' => $pesanan->tanggal_pemesanan
                    ],
                    [
                        'status' => $pesanan->metode_pengiriman == 'Delivery' ? 'Proses pengantaran' : 'Siap Diambil',
                        'completed' => in_array($pesanan->status, ['Proses pengantaran', 'Siap Diambil', 'Selesai']),
                        'timestamp' => $pesanan->tanggal_pembayaran
                    ],
                    [
                        'status' => 'Selesai',
                        'completed' => $pesanan->status == 'Selesai',
                        'timestamp' => $pesanan->status == 'Selesai' ? $pesanan->updated_at : null
                    ]
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $result
        ]);
    }

    /**
     * Get order history for guest users by order ID and phone number
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackGuestOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'phone' => 'required|string'
        ]);

        $pesanan = Pesanan::with(['detailPesanan.produk', 'pembayaran'])
            ->where('id', $request->order_id)
            ->where('no_hp', $request->phone)
            ->first();

        if (!$pesanan) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan tidak ditemukan. Pastikan ID pesanan dan nomor telepon benar.'
            ], 404);
        }

        // Use the same formatting as in show() method
        // Format response data
        $orderDetails = [];

        foreach ($pesanan->detailPesanan as $detail) {
            $orderDetails[] = [
                'produk_id' => $detail->id_produk,
                'nama_produk' => $detail->produk->nama_produk,
                'jumlah' => $detail->jumlah,
                'harga' => (int)$detail->harga,
                'subtotal' => (int)$detail->subtotal
            ];
        }

        // Get payment details
        $paymentDetails = null;
        if ($pesanan->pembayaran) {
            $paymentDetails = [
                'status' => $pesanan->pembayaran->status_pemrosesan,
                'metode' => $pesanan->pembayaran->metode,
                'tanggal_pembayaran' => $pesanan->pembayaran->tanggal_pembayaran,
                'payment_details' => $pesanan->payment_details ? json_decode($pesanan->payment_details) : null
            ];
        }

        $result = [
            'id' => $pesanan->id,
            'tanggal_pemesanan' => $pesanan->tanggal_pemesanan,
            'tanggal_pengiriman' => $pesanan->tanggal_pengiriman,
            'total_bayar' => (int)$pesanan->total_bayar,
            'status' => $pesanan->status,
            'metode_pembayaran' => $pesanan->metode_pembayaran,
            'metode_pengiriman' => $pesanan->metode_pengiriman,
            'nama_penerima' => $pesanan->nama,
            'no_hp' => $pesanan->no_hp,
            'alamat_pengiriman' => $pesanan->alamat_pengiriman,
            'lokasi_maps' => $pesanan->lokasi_maps,
            'items' => $orderDetails,
            'payment' => $paymentDetails,
            'tracking' => [
                'current_status' => $pesanan->status,
                'steps' => [
                    [
                        'status' => 'Mempersiapkan',
                        'completed' => true,
                        'timestamp' => $pesanan->tanggal_pemesanan
                    ],
                    [
                        'status' => $pesanan->metode_pengiriman == 'Delivery' ? 'Proses pengantaran' : 'Siap Diambil',
                        'completed' => in_array($pesanan->status, ['Proses pengantaran', 'Siap Diambil', 'Selesai']),
                        'timestamp' => $pesanan->tanggal_pembayaran
                    ],
                    [
                        'status' => 'Selesai',
                        'completed' => $pesanan->status == 'Selesai',
                        'timestamp' => $pesanan->status == 'Selesai' ? $pesanan->updated_at : null
                    ]
                ]
            ]
        ];

        return response()->json([
            'success' => true,
            'message' => 'Detail pesanan berhasil diambil',
            'data' => $result
        ]);
    }
}
