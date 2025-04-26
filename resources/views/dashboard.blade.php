@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="section-content section-dashboard-home" data-aos="fade-up">
        <div class="container-fluid">
            <div class="dashboard-heading">
                <h2 class="dashboard-title">Dashboard</h2>
                <p class="dashboard-subtitle">Laporan Penjualan Dan Pendapatan</p>
                <h5 class="font-weight-bold mt-2">UD. AYAM POTONG RIZKY</h5>
            </div>

            <!-- Filter Tanggal -->
            <div class="row mb-3 mt-3">
                <div class="col-md-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <form action="{{ route('dashboard') }}" method="GET" class="row align-items-end">
                                <div class="col-md-4">
                                    <label for="tanggal_mulai">Tanggal Mulai</label>
                                    <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai"
                                        value="{{ request('tanggal_mulai', now()->subDays(30)->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="tanggal_akhir">Tanggal Akhir</label>
                                    <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir"
                                        value="{{ request('tanggal_akhir', now()->format('Y-m-d')) }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">Terapkan Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kartu Ringkasan -->
            <div class="dashboard-content">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card mb-3 border-left-primary shadow">
                            <div class="card-body">
                                <div class="dashboard-card-title">Total Pelanggan</div>
                                <div class="dashboard-card-subtitle">{{ $totalPelanggan }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3 border-left-success shadow">
                            <div class="card-body">
                                <div class="dashboard-card-title">Total Pendapatan</div>
                                <div class="dashboard-card-subtitle">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3 border-left-info shadow">
                            <div class="card-body">
                                <div class="dashboard-card-title">Total Transaksi</div>
                                <div class="dashboard-card-subtitle">{{ $totalTransaksi }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card mb-3 border-left-warning shadow">
                            <div class="card-body">
                                <div class="dashboard-card-title">Produk Terjual</div>
                                <div class="dashboard-card-subtitle">{{ $produkTerjual }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grafik Pendapatan -->
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Grafik Pendapatan {{ $periodeLabel }}</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-area">
                                    <canvas id="revenueChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laporan Penjualan Terbaru -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Penjualan Terbaru</h6>
                                <a href="{{ route('pesanan.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Tanggal</th>
                                                <th>Pelanggan</th>
                                                <th>Status</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($pesananTerbaru as $pesanan)
                                                <tr>
                                                    <td>
                                                        <a href="{{ route('pesanan.show', $pesanan->id) }}">#{{ $pesanan->id }}</a>
                                                    </td>
                                                    <td>{{ $pesanan->tanggal_pemesanan->format('d/m/Y') }}</td>
                                                    <td>
                                                        @if($pesanan->pelanggan)
                                                            {{ $pesanan->pelanggan->nama }}
                                                        @else
                                                            {{ $pesanan->nama ?: 'Guest' }}
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if($pesanan->status == 'Mempersiapkan')
                                                            <span class="badge badge-info">Mempersiapkan</span>
                                                        @elseif($pesanan->status == 'Proses pengantaran')
                                                            <span class="badge badge-primary">Proses pengantaran</span>
                                                        @elseif($pesanan->status == 'Siap Diambil')
                                                            <span class="badge badge-warning">Siap Diambil</span>
                                                        @elseif($pesanan->status == 'Selesai')
                                                            <span class="badge badge-success">Selesai</span>
                                                        @elseif($pesanan->status == 'Dibatalkan')
                                                            <span class="badge badge-danger">Dibatalkan</span>
                                                        @endif
                                                    </td>
                                                    <td>Rp {{ number_format($pesanan->total_bayar, 0, ',', '.') }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">Tidak ada data penjualan</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rekap Harga Produk -->
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Rekap Harga Produk Hari Ini</h6>
                                <a href="{{ route('produk.index') }}" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Produk</th>
                                                <th>Harga</th>
                                                <th>Stok</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($produkTerbaru as $produk)
                                                <tr>
                                                    <td>{{ $produk->nama_produk }}</td>
                                                    <td>Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                                                    <td>{{ $produk->stok }} {{ $produk->satuan }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center">Tidak ada data produk</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Produk Terlaris -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Produk Terlaris</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nama Produk</th>
                                                <th>Total Terjual</th>
                                                <th>Total Pendapatan</th>
                                                <th>Stok Tersisa</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($produkTerlaris as $item)
                                                <tr>
                                                    <td>{{ $item->nama_produk }}</td>
                                                    <td>{{ $item->total_terjual }} {{ $item->satuan }}</td>
                                                    <td>Rp {{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                                                    <td>{{ $item->stok }} {{ $item->satuan }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center">Tidak ada data produk terlaris</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Data untuk grafik pendapatan
    var ctx = document.getElementById('revenueChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartLabels) !!},
            datasets: [{
                label: 'Pendapatan',
                data: {!! json_encode($chartData) !!},
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                borderColor: 'rgba(78, 115, 223, 1)',
                pointRadius: 3,
                pointBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointBorderColor: 'rgba(78, 115, 223, 1)',
                pointHoverRadius: 5,
                pointHoverBackgroundColor: 'rgba(78, 115, 223, 1)',
                pointHoverBorderColor: 'rgba(78, 115, 223, 1)',
                pointHitRadius: 10,
                pointBorderWidth: 2,
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Pendapatan: Rp ' + context.raw.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
