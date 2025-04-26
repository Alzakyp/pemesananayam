<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\DetailPesanan;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filter tanggal
        $tanggalMulai = $request->input('tanggal_mulai', Carbon::now()->subDays(30)->format('Y-m-d'));
        $tanggalAkhir = $request->input('tanggal_akhir', Carbon::now()->format('Y-m-d'));

        // Convert to Carbon objects for comparison
        $startDate = Carbon::parse($tanggalMulai)->startOfDay();
        $endDate = Carbon::parse($tanggalAkhir)->endOfDay();

        // Label periode
        $periodeLabel = '';
        if ($startDate->format('Y-m-d') == $endDate->format('Y-m-d')) {
            $periodeLabel = 'pada ' . $startDate->format('d M Y');
        } else {
            $periodeLabel = 'dari ' . $startDate->format('d M Y') . ' sampai ' . $endDate->format('d M Y');
        }

        // Total pelanggan
        $totalPelanggan = User::where('role', 'pelanggan')->count();

        // PERBAIKAN: Gunakan query yang lebih sederhana untuk menghitung total pendapatan
        // Termasuk status 'Siap Diambil' untuk pesanan pickup
        $totalPendapatan = DB::table('pesanan')
            ->whereBetween('tanggal_pemesanan', [$startDate, $endDate])
            ->whereIn('status', ['Selesai', 'Proses pengantaran', 'Siap Diambil'])
            ->sum('total_bayar');

        // Total transaksi dalam periode
        $totalTransaksi = Pesanan::whereBetween('tanggal_pemesanan', [$startDate, $endDate])
            ->where('status', '!=', 'Dibatalkan')
            ->count();

        // Total produk terjual dalam periode
        $produkTerjual = DetailPesanan::whereHas('pesanan', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('tanggal_pemesanan', [$startDate, $endDate])
                ->where('status', '!=', 'Dibatalkan');
        })
            ->sum('jumlah');

        // Pesanan terbaru dalam periode
        $pesananTerbaru = Pesanan::with(['pelanggan', 'pembayaran'])
            ->whereBetween('tanggal_pemesanan', [$startDate, $endDate])
            ->orderBy('tanggal_pemesanan', 'desc')
            ->limit(10)
            ->get();

        // Produk terbaru dan harga hari ini
        $produkTerbaru = Produk::orderBy('updated_at', 'desc')->limit(8)->get();

        // Produk terlaris
        $produkTerlaris = DB::table('detail_pesanan')
            ->join('produk', 'detail_pesanan.id_produk', '=', 'produk.id')
            ->join('pesanan', 'detail_pesanan.id_pesanan', '=', 'pesanan.id')
            ->select(
                'produk.nama_produk',
                'produk.satuan',
                'produk.stok',
                DB::raw('SUM(detail_pesanan.jumlah) as total_terjual'),
                DB::raw('SUM(detail_pesanan.jumlah * detail_pesanan.harga) as total_pendapatan')
            )
            ->whereBetween('pesanan.tanggal_pemesanan', [$startDate, $endDate])
            ->where('pesanan.status', '!=', 'Dibatalkan')
            ->groupBy('produk.id', 'produk.nama_produk', 'produk.satuan', 'produk.stok')
            ->orderBy('total_terjual', 'desc')
            ->limit(10)
            ->get();

        // Buat rentang tanggal untuk chart
        $dateRange = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dateRange[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }

        // Query untuk data pendapatan per hari dari pesanan langsung
        // PERBAIKAN: Query untuk chart pendapatan termasuk status 'Siap Diambil'
        $pendapatanPerHari = DB::table('pesanan')
            ->select(
                DB::raw('DATE(tanggal_pemesanan) as tanggal'),
                DB::raw('SUM(total_bayar) as total')
            )
            ->whereBetween('tanggal_pemesanan', [$startDate, $endDate])
            ->whereIn('status', ['Selesai', 'Proses pengantaran', 'Siap Diambil'])
            ->groupBy('tanggal')
            ->pluck('total', 'tanggal')
            ->toArray();

        // Siapkan data untuk chart
        $chartLabels = [];
        $chartData = [];

        foreach ($dateRange as $date) {
            $chartLabels[] = Carbon::parse($date)->format('d/m');
            $chartData[] = $pendapatanPerHari[$date] ?? 0;
        }

        return view('dashboard', compact(
            'totalPelanggan',
            'totalPendapatan',
            'totalTransaksi',
            'produkTerjual',
            'pesananTerbaru',
            'produkTerbaru',
            'produkTerlaris',
            'periodeLabel',
            'chartLabels',
            'chartData'
        ));
    }
}
